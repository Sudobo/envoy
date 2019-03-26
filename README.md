# [SUDOBO](http://sudobo.com) auto deployment script


[Laravel Envoy](https://laravel.com/docs/5.7/envoy) provides a clean, minimal syntax for defining common tasks you run on your remote servers. Using Blade style syntax, you can easily setup tasks for deployment, Artisan commands, and more.


**OS**

MacOS, Linux systems.


**ARGUMENTS**

    task        Call name of task declaration.
    
**OPTIONS**

|    Name   |              Description                  |    Default    |
| --------- | :--------------------------------         | :----------:|
| --on        | Name of environment                       | Required |
| --repo      | Link of your repository                   |  |
| --branch    | Name of branch on your repository         | master |
| --migrate   | With migrate                              |  No |
| --refresh   | With refresh database (--migrate is required) | No |
| --seed      | With seeder data (--migrate is required)  | No |
| --test      | Run test script                               | No |
| --password  | Password for sudo user run command         | No |
| --app_dir   | Application absolute path on environment   | /var/www/html |
| --reboot    | Need reboot service                          | No |
| --continue   |     Continue running even if a task fails | |
| --pretend    |        Dump Bash script for inspection | |
| --path=PATH  |     The path to the Envoy.blade.php file | |
| --conf=CONF  |     The name of the Envoy file | Envoy.blade.php |
|  -h, --help     |       Display this help message | |
|  -q, --quiet    |       Do not output any message | |
|  -V, --version  |       Display this application version ||
|      --ansi     |       Force ANSI output | |
|      --no-ansi  |       Disable ANSI output | |
|  -n, --no-interaction | Do not ask any interactive question | |
|  -v/vv/vvv, --verbose | Increase the verbosity of messages: 1 for normal output, 2 for more verbose output and 3 for debug | |

**ENVIRONMENT**
Next, we'll setting up delivery environment, but we need add environment variables:

``` 
APP_NAME=Laravel
APP_REPO=git:abc.git
SLACK_HOOK=notify
SLACK_CHANEL=tech
DEPLOY_DIR=/var/www/html/
DEPLOYER=deployer
DEPLOY_HOST=your_server_ip
```

For delivery our branch of coding.

```
envoy story_name --on=your_environment --branch=your_branch
```

**SETUP**

Sometimes, you may need to execute some PHP code before executing your Envoy tasks. You may use the @setup directive to declare variables and do other general PHP work before any of your other tasks are executed:

```
@setup
    $now = new DateTime();

@endsetup
```

**TASKS**

Your `@task` declarations, you should place the Bash code that should run on your server when the task is executed.


```
@task('foo', ['on' => 'web'])
    ls -la
@endtask
```

**STORIES**

Stories group a set of tasks under a single, convenient name, allowing you to group small, focused tasks into large tasks.


```
@story('deploy')
    task_name
@endstory
```

**Reference**


[Envoy](https://laravel.com/docs/5.7/envoy)


**Contributors**

SUDOBO'staff
