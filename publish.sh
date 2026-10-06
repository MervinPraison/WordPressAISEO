#!/bin/bash

# AISEO / Praison AI SEO - WordPress.org SVN Publisher
# This script publishes the plugin to WordPress.org SVN repositories
#
# Usage: ./publish.sh [version] [--dry-run]
# Example: ./publish.sh 5.0.4
#          ./publish.sh 5.0.4 --dry-run

set -e

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Configuration
PLUGIN_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
SVN_USERNAME="mervinpraison"

# WordPress.org checkout for the seo-wordpress slug.
SVN_DIR="/Users/praison/seo-wordpress-svn"
SVN_MAIN_FILE="seo-wordpress.php"
SVN_PLUGIN_NAME="Praison AI SEO"

# Files/folders to exclude from SVN
EXCLUDES=(
    '.git'
    'node_modules'
    'tests'
    '.env'
    '.env.*'
    '.vscode'
    '*.md'
    '.distignore'
    '.gitignore'
    'add-logging.js'
    'api-index.jsonl'
    'publish.sh'
    'build-wordpress-org.sh'
    '.DS_Store'
    '*.zip'
    '*.log'
)

# Function to print colored output
print_status() {
    echo -e "${BLUE}[INFO]${NC} $1"
}

print_success() {
    echo -e "${GREEN}[SUCCESS]${NC} $1"
}

print_warning() {
    echo -e "${YELLOW}[WARNING]${NC} $1"
}

print_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

# Function to get current version from seo-wordpress.php
get_current_version() {
    grep -E 'Version:[[:space:]]*[0-9]+\.[0-9]+\.[0-9]+' "$PLUGIN_DIR/seo-wordpress.php" | head -1 | grep -Eo '[0-9]+\.[0-9]+\.[0-9]+'
}

# GNU sed accepts -i. BSD sed on macOS requires -i ''.
sed_inplace() {
    if sed --version >/dev/null 2>&1; then
        sed -i "$@"
    else
        sed -i '' "$@"
    fi
}

# Function to validate version format
validate_version() {
    if [[ ! $1 =~ ^[0-9]+\.[0-9]+\.[0-9]+$ ]]; then
        print_error "Invalid version format: $1. Expected format: X.Y.Z"
        exit 1
    fi
}

# Function to update version in files
update_version() {
    local new_version=$1
    local current_version=$(get_current_version)
    
    print_status "Updating version from $current_version to $new_version..."
    
    # Update aiseo.php
    sed_inplace "s/Version: $current_version/Version: $new_version/" "$PLUGIN_DIR/aiseo.php"
    sed_inplace "s/define('AISEO_VERSION', '$current_version')/define('AISEO_VERSION', '$new_version')/" "$PLUGIN_DIR/aiseo.php"
    
    # Update seo-wordpress.php
    sed_inplace "s/Version: $current_version/Version: $new_version/" "$PLUGIN_DIR/seo-wordpress.php"
    sed_inplace "s/define('AISEO_VERSION', '$current_version')/define('AISEO_VERSION', '$new_version')/" "$PLUGIN_DIR/seo-wordpress.php"
    
    # Update readme.txt
    sed_inplace "s/Stable tag: $current_version/Stable tag: $new_version/" "$PLUGIN_DIR/readme.txt"
    
    print_success "Version updated to $new_version"
}

# Function to build rsync exclude arguments
build_excludes() {
    local excludes=""
    for exclude in "${EXCLUDES[@]}"; do
        excludes="$excludes --exclude='$exclude'"
    done
    echo "$excludes"
}

# Function to publish to a single SVN repo
publish_to_svn() {
    local repo_name=$1
    local svn_dir=$2
    local main_file=$3
    local version=$4
    local dry_run=$5
    local plugin_name=$6
    
    print_status "Publishing to $repo_name ($plugin_name)..."
    
    # Check if SVN directory exists
    if [ ! -d "$svn_dir" ]; then
        print_warning "SVN directory not found: $svn_dir. Skipping $repo_name."
        return 1
    fi
    
    # Sync files to SVN trunk
    print_status "Syncing files to $svn_dir/trunk/..."
    
    local excludes=$(build_excludes)
    eval rsync -av $excludes --delete "$PLUGIN_DIR/" "$svn_dir/trunk/"
    
    # Remove the other main file (keep only the one for this repo)
    if [ "$main_file" == "seo-wordpress.php" ]; then
        rm -f "$svn_dir/trunk/aiseo.php"
    elif [ "$main_file" == "aiseo.php" ]; then
        rm -f "$svn_dir/trunk/seo-wordpress.php"
    fi
    
    # Remove any remaining dev files
    rm -f "$svn_dir/trunk/.distignore" \
          "$svn_dir/trunk/.gitignore" \
          "$svn_dir/trunk/add-logging.js" \
          "$svn_dir/trunk/api-index.jsonl" \
          "$svn_dir/trunk/publish.sh"
    
    # Update plugin name in readme.txt for this repo
    print_status "Updating readme.txt plugin name to: $plugin_name"
    sed_inplace "s/^=== .* ===$/=== $plugin_name ===/" "$svn_dir/trunk/readme.txt"
    
    # Update first line description to match plugin name
    sed_inplace "s/^AISEO is a powerful/$plugin_name is a powerful/" "$svn_dir/trunk/readme.txt"
    sed_inplace "s/^Praison AI SEO is a powerful/$plugin_name is a powerful/" "$svn_dir/trunk/readme.txt"
    
    # Check SVN status for trunk only. Do not add stray files elsewhere in the checkout.
    cd "$svn_dir"
    
    # Add new files. macOS xargs has no -r, so only call it when there are paths.
    local added
    added=$(svn status trunk | awk '/^\?/ {print $2}')
    if [ -n "$added" ]; then
        echo "$added" | xargs svn add
    fi
    
    # Remove deleted files
    local removed
    removed=$(svn status trunk | awk '/^!/ {print $2}')
    if [ -n "$removed" ]; then
        echo "$removed" | xargs svn rm
    fi
    
    local changes=$(svn status trunk | wc -l)
    
    if [ "$changes" -eq 0 ]; then
        print_warning "No changes to commit for $repo_name"
        return 0
    fi
    
    print_status "Changes detected: $changes files"
    svn status trunk
    
    if [ "$dry_run" == "true" ]; then
        print_warning "[DRY RUN] Would commit to $repo_name trunk and create tag $version"
        return 0
    fi
    
    # Stored Subversion credentials are used when SVN_PASSWORD is unset.
    local auth_args=(--username "$SVN_USERNAME" --non-interactive)
    if [ -n "$SVN_PASSWORD" ]; then
        auth_args+=(--password "$SVN_PASSWORD")
    fi
    
    # Commit trunk, then copy that trunk to the release tag.
    print_status "Committing trunk..."
    svn ci trunk -m "Version $version" "${auth_args[@]}"
    
    # Create tag from the committed trunk. Commit only that tag.
    print_status "Creating tag $version..."
    svn cp trunk "tags/$version"
    svn ci "tags/$version" -m "Tagging version $version" "${auth_args[@]}"
    
    print_success "Published $repo_name version $version"
    
    cd "$PLUGIN_DIR"
}

# Main function
main() {
    local version=""
    local dry_run="false"
    local update_ver="false"
    
    # Parse arguments
    for arg in "$@"; do
        case $arg in
            --dry-run)
                dry_run="true"
                ;;
            --update-version)
                update_ver="true"
                ;;
            *)
                if [[ $arg =~ ^[0-9]+\.[0-9]+\.[0-9]+$ ]]; then
                    version=$arg
                fi
                ;;
        esac
    done
    
    # Get version
    if [ -z "$version" ]; then
        version=$(get_current_version)
        print_status "Using current version: $version"
    else
        validate_version "$version"
        if [ "$update_ver" == "true" ] || [ "$version" != "$(get_current_version)" ]; then
            update_version "$version"
        fi
    fi
    
    echo ""
    echo "=========================================="
    echo "  WordPress.org SVN Publisher"
    echo "  Version: $version"
    if [ "$dry_run" == "true" ]; then
        echo "  Mode: DRY RUN"
    fi
    echo "=========================================="
    echo ""
    
    # Publish the seo-wordpress checkout. Only trunk is synced and committed.
    if publish_to_svn "seo-wordpress" "$SVN_DIR" "$SVN_MAIN_FILE" "$version" "$dry_run" "$SVN_PLUGIN_NAME"; then
        echo "=========================================="
        print_success "Published seo-wordpress version $version"
        echo "=========================================="
    else
        print_error "Publishing seo-wordpress failed"
        exit 1
    fi
}

# Run main function
main "$@"
