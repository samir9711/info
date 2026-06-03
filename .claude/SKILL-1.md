---
name: review-existing
description: Review the existing Laravel and React Native codebase against the project architecture and coding standards without modifying files.
---

# Review Existing Codebase

Read these files first:

1. CLAUDE.md
2. docs/mall-mobile-app-documentation.md
3. .claude/ARCHITECTURE.md
4. .claude/CODING_STANDARDS.md
5. .claude/LARAVEL_BEST_PRACTICES.md
6. .claude/REACT_NATIVE_PATTERNS.md
7. .claude/SECURITY_GUIDELINES.md

## Task

Review the existing codebase only.

Do not modify files.

## Check

- Architecture violations.
- Business logic inside controllers.
- Database queries inside controllers.
- Missing FormRequests.
- Missing DTOs.
- Missing Services.
- Missing Repositories.
- Missing API Resources.
- Missing Policies.
- Missing authorization.
- Missing ownership checks.
- Missing transactions.
- Missing custom exceptions.
- Missing tests.
- Unsafe logs.
- Inconsistent API responses.
- N+1 query risks.
- Missing pagination.
- Missing indexes if obvious from migrations.

## Output Format

Return:

1. Executive summary.
2. File-by-file problems.
3. Severity for each problem:
    - critical
    - high
    - medium
    - low
4. Exact recommended fix.
5. Safe refactor order.
6. Files that should not be changed.
7. Tests that must be added.

## Hard Rule

Do not edit files.