module.exports = {
    apps: [
        {
            name: "laravel-horizon-cookie", // The name of the application (can be any meaningful name)
            script: "php", // The script to run (in this case, 'php')
            args: "artisan horizon", // The arguments to pass to the script ('artisan horizon' in this case)
            env: {
                NODE_ENV: "production", // Environment variables for the 'production' environment
            },
            env_staging: {
                NODE_ENV: "staging", // Environment variables for the 'staging' environment
            },
        },
    ],
};
