---
name: continue-feature
description: Continue implementing a feature using the project's Laravel and React Native architecture rules.
---

# Continue Feature

Read first:

1. CLAUDE.md
2. docs/mall-mobile-app-documentation.md
3. .claude/ARCHITECTURE.md
4. .claude/CODING_STANDARDS.md
5. .claude/LARAVEL_BEST_PRACTICES.md
6. .claude/REACT_NATIVE_PATTERNS.md
7. .claude/SECURITY_GUIDELINES.md

## Workflow

Before coding:

1. Inspect existing files related to the feature.
2. Identify what already exists.
3. Identify what is missing.
4. Do not duplicate classes.
5. Do not overwrite unrelated code.
6. Produce a file-by-file plan.

## Laravel Requirements

- Controller must be thin.
- Validation must be in FormRequest.
- Complex input must use DTO.
- Business logic must be in Service.
- Single-purpose operations must be Actions.
- Data access must be Repository.
- Output must use Resource.
- Authorization must use Policy or explicit authorization.
- Multi-step writes must use DB transaction.
- Business errors must use custom Exceptions.
- Side effects must use Events, Listeners, Jobs, or Notifications.
- Tests must be added or updated.

## React Native Requirements

- Use TypeScript.
- No any.
- No API calls inside screens.
- Use services for API calls.
- Use hooks for reusable data logic.
- Handle loading, error, empty, and success states.

## Output Format

Return:

1. Existing files detected.
2. Missing files.
3. Implementation plan.
4. Changed files in full.
5. New files in full.
6. Tests in full.
7. Commands to run.

## Hard Rules

- Do not write pseudo-code.
- Do not omit imports.
- Do not leave TODOs.
- Do not say "same as above".