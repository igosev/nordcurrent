# Nordcurrent task
## Requirements for environment

* Composer 2.*
* Phpunit 9.5
* Php 8.1

### Installation and running

Install by running:

```
composer install
```

```
composer dumpautoload -o
```

```
./vendor/bin/phpunit tests
```

## Notes
- Had to change folder names (used uppercase first letters) in ```src``` directory, because of thrown parsing errors while using ```composer dumpautoload -o```.
- Implementation of task is in ```tests/ApiTest.php``` file.
- For Test Case 4 had to add
    ```
    $player->setSessionId($this->sid);
    ```
  on line 141 in ```src/Api/Server.php``` file, for test case to work properly.