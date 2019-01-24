@servers(['development' => 'user@server', 'production' => 'user@server'])

{{-- Configuration section --}}
@setup

/*
|--------------------------------------------------------------------------
| Git Config
|--------------------------------------------------------------------------
|
| The git repository location.
|
*/

$repo = ''; //configure the repo uri
$branch = isset($branch) ? $branch : "master";


/*
|--------------------------------------------------------------------------
| Server Paths
|--------------------------------------------------------------------------
|
| The base paths where the deployment will happens.
|
*/

if (!isset($app_dir)) {
    $app_dir = '/var/www/html';
}

$releases_dir =  $app_dir . '/releases';
$release_dir  =  $app_dir . '/releases/' . date('YmdHis');


/*
|--------------------------------------------------------------------------
| Num of releases
|--------------------------------------------------------------------------
|
| The number of releases to keep.
|
*/

$keep = 3;


/*
|--------------------------------------------------------------------------
| Writable resources
|--------------------------------------------------------------------------
|
| Define the resources that needs writable permissions.
|
*/
$writable = [
'storage'
];


/*
|--------------------------------------------------------------------------
| Sharable resources
|--------------------------------------------------------------------------
|
| Define a associative array with the resources to be shared across releases.
| The value of a element in the array can only be 'd' for directories or 'f' for files.
|
*/
$shared = [
'storage' => 'd',
'.env' => 'f',
];

/*---- Check for required params ----*/
if ( ! isset($on) ) {
throw new Exception('The --on option is required.');
}

/* Set default value for args */
$seed = isset($seed) ? '--seed' : '';

@endsetup

{{-- Deployment macro, use to deploy a new version of a existent project --}}
@story('app:deploy', ['on' => $on])
    clone
    composer:install
    assets:install
    symlinks
    migrate
    tests
    clean
@endstory

{{-- Install macro, use to deploy a new version of a non-existent project --}}
@story('app:install', ['on' => $on])
    clone
    composer:install 
    assets:install
    assets:build
    permissions
    symlinks
    migrate
    tests
    clean
@endstory

{{-- Clone task, creates release directory, then clones into it --}}
@task('clone', ['on' => $on])
    eval "$(ssh-agent -s)";
    [ -d {{ $releases_dir  }} ] || mkdir -p {{ $releases_dir }};
    git clone {{ $repo }}  --branch={{ $branch }} {{ $release_dir }};
    echo "Repository has been cloned";
@endtask

{{-- Updates composer, then runs a fresh installation --}}
@task('composer:install', ['on' => $on])
    cd {{ $release_dir }};
    composer install --prefer-dist --no-interaction;

    composer dumpautoload;

    echo "Composer dependencies have been installed";
@endtask

{{-- Migrate the databases --}}
@task('migrate', ['on' => $on])
    @if(isset($m))
        php {{ $release_dir }}/artisan migrate --force {{$seed}} --no-interaction;
    @else
        echo "No migration found.";
    @endif
@endtask

{{-- Migrate and refresh the database --}}
@task('migrate:refresh', ['on' => $on])
    php {{ $release_dir }}/artisan migrate:refresh {{ $seed }} --force --no-interaction;
@endtask

{{-- Set permissions for various files and directories --}}
@task('permissions', ['on' => $on])
    @if(isset($password))
        @foreach($writable as $item)
            echo "{{ $password }}" | sudo -S chown -R www-data {{ $release_dir }}/{{ $item }};
        @endforeach
    @else
        @foreach($writable as $item)
            chown -R www-data {{ $release_dir }}/{{ $item }};
        @endforeach
    @endif
    echo "Permissions have been set for  {{ $release_dir }}/{{ $item }}";
@endtask

{{-- Install frotend assets --}}
@task('assets:install', ['on' => $on])
    @if(isset($a))
        cd {{ $release_dir }};
        npm install;
        bower install;
    @else
        echo "No asset installation.";
    @endif
@endtask

{{-- Build frotend assets --}}
@task('assets:build', ['on' => $on])
    @if(isset($a))
        cd {{ $release_dir }};
        gulp;
    @else
        echo "No assset built.";
    @endif
@endtask

{{-- Run tests--}}
@task('tests', ['on' => $on])
    @if(isset($i))
        {{ $release_dir }}/vendor/bin/phpunit {{ $release_dir }};
    @else
        echo "No line has tested.";
    @endif
@endtask

{{-- Clean old releases --}}
@task('clean', ['on' => $on])
    echo "Clean old releases";
    cd {{ $releases_dir }};
    @if(isset($password))
        echo "{{ $password }}" | sudo -S rm -rf $(ls -t | tail -n +{{ $keep }});
    @else
        rm -rf $(ls -t | tail -n +{{ $keep }});
    @endif
@endtask

{{-- Configure shared assets --}}
@task('symlinks', ['on' => $on])
    [ -d {{ $app_dir }}/shared ] || mkdir -p {{ $app_dir }}/shared;

    @foreach($shared as $item => $type)

        #// if the item passed exists in the shared folder and in the release folder then
        #// remove it from the release folder;
        #// or if the item passed not existis in the shared folder and existis in the release folder then
        #// move it to the shared folder

        if ( [ -{{ $type }} '{{ $app_dir }}/shared/{{ $item }}' ] && [ -{{ $type }} '{{ $release_dir }}/{{ $item }}' ] );
        then
        rm -rf {{ $release_dir }}/{{ $item }};
        echo "rm -rf {{ $release_dir }}/{{ $item }}";
        elif ( [ ! -{{ $type }} '{{ $app_dir }}/shared/{{ $item }}' ]  && [ -{{ $type }} '{{ $release_dir }}/{{ $item }}' ] );
        then
        mv {{ $release_dir }}/{{ $item }} {{ $app_dir }}/shared/{{ $item }};
        echo "mv {{ $release_dir }}/{{ $item }} {{ $app_dir }}/shared/{{ $item }}";
        fi

        ln -nfs {{ $app_dir }}/shared/{{ $item }} {{ $release_dir }}/{{ $item }};
        echo "Symlink has been set for {{ $release_dir }}/{{ $item }}";
    @endforeach

    ln -nfs {{ $release_dir }} {{ $app_dir }}/current;
    echo "{{ $password }}" | sudo -S chown -R www-data {{ $app_dir }}/current;
    echo "All symlinks have been set";
@endtask