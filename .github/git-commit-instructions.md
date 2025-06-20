# Git Commit Message Instructions

## Conventional Commit Format

This project recommends using the [Conventional Commit](https://www.conventionalcommits.org/) format for all commit messages. This helps keep the commit history readable and enables automated tools for changelogs and releases.

### Commit Message Structure

```
<type>[optional scope]: <description>

[optional body]

[optional footer(s)]
```

- **type**: The kind of change (e.g., `feat`, `fix`, `docs`, `style`, `refactor`, `test`, `chore`)
- **optional scope**: A section of the codebase affected (e.g., `cache`, `command`, `docs`)
- **description**: Short summary of the change (imperative, lower case, no period)

### Examples

- `feat: add user login functionality`
- `fix(cache): correct total price calculation`
- `docs: update README with installation steps`

### Optional Body

Use the body to provide additional context about the change.

### Optional Footer

Use the footer to reference issues or describe breaking changes.

```
BREAKING CHANGE: changes the API of the cache command

Closes #123
```

### Benefits

- Clear, consistent commit history
- Easier automation for changelogs and releases
- Communicates intent of changes to collaborators

For more details, see [conventionalcommits.org](https://www.conventionalcommits.org/).

