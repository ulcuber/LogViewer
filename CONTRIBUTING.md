# CONTRIBUTING

Contributions are welcome, and are accepted via pull requests. Please review these guidelines before submitting any pull requests.

## Guidelines

  * Please follow the [PSR-12](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-12-extended-coding-style-guide.md) Coding Standard
  * Ensure that the current tests pass, and if you've added something new, add the tests where relevant.
  * Remember that we follow SemVer. If you are changing the behavior, or the public api, you may need to update the docs.
  * Send a coherent commit history, making sure each individual commit in your pull request is meaningful. If you had to make multiple intermediate commits while developing, please squash them before submitting.
  * You may also need to rebase to avoid merge conflicts.
  * **Translations :**
    * After you made your translations, ensure that you added your locale key in alphabetical order at [line](https://github.com/ulcuber/LogViewer/blob/v9.x/tests/TestCase.php#L31) in the `tests/TestCase.php` file for the tests.
    * The locale key must be a [ISO 639-1 code](https://en.wikipedia.org/wiki/List_of_ISO_639-1_codes), check also [caouecs/Laravel-lang package](https://github.com/caouecs/Laravel-lang/).

## Running Tests

You will need an install of [Composer](https://getcomposer.org) before continuing.

First, install the dependencies:

```bash
composer install
```

Then run phpunit:

```bash
vendor/bin/phpunit
```

If the test suite passes on your local machine you should be good to go.

When you make a pull request, the tests will automatically be run again by Github Actions
