# GitHub Auto-Deploy for ServerPilot

Deploy your repositories automatically from GitHub to ServerPilot apps. This script can be easily modified to suit most LAMP or LEMP environments, not just ServerPilot.

I also have a version of this script that works for BitBucket, you can find it here: https://bitbucket.org/matt-stone/bitbucket-auto-deploy/

## Getting Started

These instructions will help you get this script deployed on a live system and the relevant webhook setup in GitHub.

### Prerequisites

What you need to install the script and how to set it up

```
ServerPilot Server with an app setup and it's SFTP credentials
GitHub account with a repository to deploy from
```

### Compatibilty

This script has been fully tested with ServerPilot using the following versions of PHP:

5.6, 7.0, 7.1, 7.2, 7.3

### Installing

By default this script will deploy to your app root directory (e.g. /srv/users/<sp_username>/apps/<sp_appname>), this is ideal for applications such as Laravel where the application files sit above the public folder that the web server has set as its document root. There is an option in the script to deploy directly to the public folder or you could even change this to a deeper path such as a WordPress theme directory (e.g. /srv/users/<sp_username>/apps/<sp_appname>/public/wp-content/themes).

1. If you haven't already generated an SSH key on your server run the following command accepting the default path and just hit enter on the passphrase option as we do not want to set a passphrase (if you've already setup SSH keys skip to step 3):
```
ssh-keygen
```

2. Output the contents of your newly created SSH file and copy this to GitHub by creating a new access key in repository Settings > General > Access keys > Add key:
```
cat .ssh/id_rsa.pub
```

3. From a terminal window ssh into your ServerPilot server and cd to your app directory (you may need to amend the path below to suit your setup, e.g. apps/APPNAME/public if you are deploying to the public directory):
```
cd apps/APPNAME
```

4. Next you need to create the hidden repo directory which is where a cached copy of the repository is stored that is where we deploy from:
```
mkdir -p .repo
```

5. Run the following command to manually pull a copy of the repository to your hidden repo directory:
```
git clone --mirror git@github.com:GITHUBUSERNAME/REPOSITORYNAME.git .repo
```

6. Do an initial deployment of the repository by running this set of commands (if you are deploying to a public directory you should add /public after APPNAME):
```
cd .repo
GIT_WORK_TREE=/srv/users/SYSUSER/apps/APPNAME git checkout -f master
```

7. The only file you need from this project is github-deploy.php, download it directly from source, rename it to something like github-deploy-bda84024ca84.php (replace bda84024ca84 with a random string of characters of your own choosing), copy it to your public directory within your app (it needs to be in this location in order for GitHub webhooks to connect to it regardless of where you actually deploy the repository to) and then using your favourite terminal editor modify the following lines to suit your deployment:

```
/* ServerPilot app details */
$sp_user_name = 'serverpilot';
$sp_app_name = 'example';

/* Branch you want to deploy from */
$branch_to_deploy = 'master';

/* Deploy to public directory only? (by default we deploy to the app directory) */
$deploy_to_public = false;

/* Run composer after deploy to update packages? */
$run_composer = true;

/* Run custom commands after deploy? */
$run_custom_commands = false;

/* Add your custom shell commands to run here and change run_custom_commands to true */
$custom_commands = '';
```

8. Add a publicly accessible link to your php file to your repository Webhooks and set it to trigger on a repository push.

That should be all you need to edit but on custom setups you might need to modify some of the paths to suit your needs.

## Authors

* Matt Stone - https://www.matt-stone.co.uk
* Craig Bowler - https://craigbowler.io

## License

This project is licensed under the GNU License - see the [LICENSE.md](LICENSE.md) file for details.

## Acknowledgments

* Based on the tutorial found at https://serverpilot.io/docs/how-to-automatically-deploy-a-git-repo-from-bitbucket which was in turn based on the tutorial found at http://jonathannicol.com/blog/2013/11/19/automated-git-deployments-from-bitbucket/ and modifed to work for GitHub.
