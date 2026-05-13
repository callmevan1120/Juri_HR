#!/usr/bin/env bash
set -euo pipefail

EVIDENCE_DIR="${EVIDENCE_DIR:-storage/review-evidence/local}"
RUN_E2E="${RUN_E2E:-0}"
RUN_APK_SMOKE="${RUN_APK_SMOKE:-0}"

mkdir -p "$EVIDENCE_DIR"

run_gate() {
  local name="$1"
  local log_file="$2"

  shift 2

  echo "==> $name"
  "$@" 2>&1 | tee "$EVIDENCE_DIR/$log_file"
}

run_gate "PHP tests" "php-artisan-test.log" php artisan test --without-tty
run_gate "PHPStan" "composer-phpstan.log" composer phpstan
run_gate "Pint" "pint-test.log" ./vendor/bin/pint --test
run_gate "Composer audit" "composer-audit.log" composer audit
run_gate "Bun audit" "bun-audit.log" bun audit
run_gate "Frontend build" "bun-run-build.log" bun run build
run_gate "RBAC audit" "php-artisan-rbac-audit.log" php artisan rbac:audit
run_gate "UI rules" "composer-check-ui.log" composer check:ui
run_gate "Modern stack" "composer-check-modern-stack.log" composer check:modern-stack
run_gate "Enterprise boundary" "composer-check-enterprise-boundary.log" composer check:enterprise-boundary

if [ "$RUN_E2E" = "1" ]; then
  run_gate "Playwright smoke" "playwright-smoke.log" bun run e2e:smoke
fi

if [ "$RUN_APK_SMOKE" = "1" ]; then
  SCREENSHOT_PATH="$EVIDENCE_DIR/apk-device-smoke.png" \
    run_gate "APK smoke" "apk-smoke.log" bun run apk:smoke
fi

{
  echo "# Local Review Evidence"
  echo
  echo "| Gate | Log |"
  echo "| --- | --- |"
  echo "| PHP tests | php-artisan-test.log |"
  echo "| PHPStan | composer-phpstan.log |"
  echo "| Pint | pint-test.log |"
  echo "| Composer audit | composer-audit.log |"
  echo "| Bun audit | bun-audit.log |"
  echo "| Frontend build | bun-run-build.log |"
  echo "| RBAC audit | php-artisan-rbac-audit.log |"
  echo "| UI rules | composer-check-ui.log |"
  echo "| Modern stack | composer-check-modern-stack.log |"
  echo "| Enterprise boundary | composer-check-enterprise-boundary.log |"

  if [ "$RUN_E2E" = "1" ]; then
    echo "| Playwright smoke | playwright-smoke.log |"
  fi

  if [ "$RUN_APK_SMOKE" = "1" ]; then
    echo "| APK smoke | apk-smoke.log, apk-device-smoke.png |"
  fi
} > "$EVIDENCE_DIR/summary.md"

echo "Review evidence written to $EVIDENCE_DIR"
