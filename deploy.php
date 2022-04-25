<?php
namespace Deployer;

require 'recipe/laravel.php';



set('application', 'dep-demo');
set('ssh_multiplexing', true); // Speed up deployment


// Set up a deployer task to copy secrets to the server. 
// Grabs the dotenv file from the github secret
task('deploy:secrets', function () {
    file_put_contents(__DIR__ . '/.env', getenv('DOT_ENV'));
    upload('.env', get('deploy_path') . '/shared');
});
// Hosts
host('test.slymdev.website/') // Name of the server
    ->hostname('[162.0.232.38]:21098') // Hostname or IP address
    ->stage('staging') // Deployment stage (production, staging, etc)
    ->user('deploy') // SSH user
    ->set('deploy_path', '/cd/test.slymdev.website/');   // Deploy path

    after('deploy:failed', 'deploy:unlock'); // Unlock after failed deploy

desc('Deploy the application');
task('deploy', [
    'deploy:info',
    'deploy:prepare',
    'deploy:lock',
    'deploy:release',
    'rsync', // Deploy code & built assets
    'deploy:secrets', // Deploy secrets
    'deploy:shared',
    'deploy:vendors',
    'deploy:writable',
    'artisan:storage:link', // |
    'artisan:view:cache',   // |
    'artisan:config:cache', // | Laravel specific steps 
    'artisan:optimize',     // |
    // 'artisan:migrate',      // |
    'deploy:symlink',
    'deploy:unlock',
    'cleanup',
]);
