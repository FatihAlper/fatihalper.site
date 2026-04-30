$ErrorActionPreference = 'Stop'

$blockedPathPattern = '^(?:\.env(?:$|\.)|content/|media/|site/accounts/|site/cache/|site/sessions/|site/logs/|site/config/\.license|_deploy|.*\.(?:zip|log)$)'
$secretPattern = '(SPOTIFY_CLIENT_SECRET\s*=\s*[^#\s]+|SPOTIFY_REFRESH_TOKEN\s*=\s*[^#\s]+|TMDB_ACCESS_TOKEN\s*=\s*[^#\s]+|K-[A-Z0-9]{10,}|BEGIN (RSA |OPENSSH |PRIVATE )?PRIVATE KEY)'

$staged = @(git diff --cached --name-only --diff-filter=ACMR)
$blocked = @($staged | Where-Object { $_ -match $blockedPathPattern })

if ($blocked.Count -gt 0) {
    Write-Error ("Blocked staged private/content/runtime files:`n" + ($blocked -join "`n"))
    exit 1
}

$secretOutput = @(git grep --cached -n -I -E $secretPattern -- . ':!kirby/**' ':!site/plugins/kirby3-dotenv/vendor/**' ':!site/plugins/timnarr-kirby-imagex-bd9abbe/**')
$secretExit = $LASTEXITCODE

if ($secretExit -eq 0) {
    Write-Error ("Blocked secret-looking values:`n" + ($secretOutput -join "`n"))
    exit 1
}

if ($secretExit -ne 1) {
    Write-Error "Secret scan failed with git grep exit code $secretExit"
    exit $secretExit
}

Write-Output 'Code-only guardrails passed.'
