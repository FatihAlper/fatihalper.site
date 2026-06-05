#!/bin/bash
set -e

# Pattern to block private, content, and runtime files
blockedPathPattern='^(\.env($|\.)|content/|media/|site/accounts/|site/cache/|site/sessions/|site/logs/|site/config/\.license|_deploy|.*\.(zip|log)$)'

# Pattern to detect secrets
secretPattern='(SPOTIFY_CLIENT_SECRET\s*=\s*[^#\s]+|SPOTIFY_REFRESH_TOKEN\s*=\s*[^#\s]+|TMDB_ACCESS_TOKEN\s*=\s*[^#\s]+|K-[A-Z0-9]{10,}|BEGIN (RSA |OPENSSH |PRIVATE )?PRIVATE KEY)'

# Get staged files
staged=$(git diff --cached --name-only --diff-filter=ACMR)

# Find blocked files
blocked=""
if [ -n "$staged" ]; then
    blocked=$(echo "$staged" | grep -E "$blockedPathPattern" || true)
fi

if [ -n "$blocked" ]; then
    echo "ERROR: Blocked staged private/content/runtime files:"
    echo "$blocked"
    exit 1
fi

# Secret scan (excluding vendor/library paths)
# git grep exits with 0 if matches are found, 1 if no matches are found, and other code on error
set +e
secretOutput=$(git grep --cached -n -I -E "$secretPattern" -- . ':!kirby/**' ':!site/plugins/kirby3-dotenv/vendor/**' ':!site/plugins/timnarr-kirby-imagex-bd9abbe/**' 2>&1)
secretExit=$?
set -e

if [ $secretExit -eq 0 ]; then
    echo "ERROR: Blocked secret-looking values:"
    echo "$secretOutput"
    exit 1
fi

if [ $secretExit -ne 1 ]; then
    echo "ERROR: Secret scan failed with git grep exit code $secretExit"
    echo "$secretOutput"
    exit $secretExit
fi

echo "Code-only guardrails passed."
