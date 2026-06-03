# Claude Project Instructions

You are working on a Laravel API + React Native mobile commerce project.

Before doing any coding task, read these files:

1. docs/mall-mobile-app-documentation.md
2. .claude/ARCHITECTURE.md
3. .claude/CODING_STANDARDS.md
4. .claude/LARAVEL_BEST_PRACTICES.md
5. .claude/REACT_NATIVE_PATTERNS.md
6. .claude/SECURITY_GUIDELINES.md
7. .claude/PROMPTS.md

## Mandatory Rules

- Do not write business logic in controllers.
- Do not query database directly inside controllers.
- Use FormRequest for validation.
- Use DTOs for complex input.
- Use Services for business logic.
- Use Actions for single-purpose operations.
- Use Repositories for data access.
- Use API Resources for output.
- Use Policies for authorization.
- Use Events and Jobs for side effects.
- Use custom Exceptions for business errors.
- Use DB transactions for multi-step writes.
- Use strict PHP types.
- Do not return raw Eloquent models from API.
- Do not skip imports.
- Do not write pseudo-code.
- Do not leave TODOs.

## Workflow

For existing code:
1. First review the current implementation.
2. Compare it against the architecture files.
3. Report problems file by file.
4. Propose a safe refactor plan.
5. Do not modify files until explicitly asked.

For new features:
1. Read documentation first.
2. Create a file-by-file plan.
3. Implement the feature completely.
4. Add validation.
5. Add authorization.
6. Add tests.
7. Run formatting and tests when possible.

## Output Requirements

When writing code:
- Return full file paths.
- Return full file contents.
- Do not say "same as above".
- Do not omit imports.
- Do not create partial code.