# Laravel + Vue Starter Kit + Modern PHP Tooling

<a href="https://herd.laravel.com/new?starter-kit=cruzmediaorg/vue-starter-kit"><img src="https://img.shields.io/badge/Install%20with%20Herd-f55247?logo=laravel&logoColor=white"></a>

## Introduction

Enhanced version of the official Laravel Vue starter kit, with 100% test coverage and modern PHP tooling.

It has all the stuff I regularly do when I start a fresh new laravel project. 

This Vue starter kit utilizes Vue 3 and the Composition API, TypeScript, Tailwind, and the [shadcn-vue](https://www.shadcn-vue.com) component library.

### Improvements
- PHPStan: Level max.
- 100% test coverage with PestPHP.
- Arch Testing.
- Rector rules for refactoring.
- Pint rules for strict typing and code consistency.
- New `make:action` command to quickly create an action class.
- Git pre-push hook to run all tests/linting/types before pushing.
- Google OAuth

## Installation

Installation can be done by laravel installer.

```bash
laravel new --using=cruzmediaorg/vue-starter-kit
```
