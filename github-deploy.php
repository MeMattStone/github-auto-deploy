<?php
/*
GitHub Auto-Deploy to ServerPilot by Matt Stone and Craig Bowler
https://github.com/MeMattStone/github-auto-deploy

Based on the tutorial at https://serverpilot.io/docs/how-to-automatically-deploy-a-git-repo-from-bitbucket
*/

/* ServerPilot app details */
$sp_user_name = 'serverpilot';
$sp_app_name = 'example';

/* Branch you want to deploy from */
$branch_to_deploy = 'master';

/* Deploy to public directory only? (by default we deploy to the app directory) */
$deploy_to_public = false;

/* Run composer install after deploy to update packages? */
$run_composer = true;

/* Run custom commands after deploy? */
$run_custom_commands = false;

/* Add your custom shell commands to run here and change run_custom_commands to true */
$custom_commands = '';

/* By default we deploy to the app directory which is great for applications like Laravel */
$app_root_dir = '/srv/users/' . $sp_user_name . '/apps/' . $sp_app_name;

/* Should we deploy to the public directory only? */
if ($deploy_to_public) {

  /* Add the public directory to the deployment path */
  $app_root_dir .= '/public';

}

/* The hidden directory that holds a copy of your repository where we actually deploy from */
$hidden_repo_dir = $app_root_dir . '/.repo';

/* Path to binary for git. This can be set to just 'git' in most cases */
$git_bin_path = 'git';

/* Get app PHP version to ensure correct version of composer is run */
$versionArray = explode('.', phpversion());
$php_version = $versionArray[0] . '.' . $versionArray[1];

/* Path to binary for composer. This is matched to your app's PHP runtime version */
$composer_bin_path = 'composer' . $php_version . '-sp';

/* Set default update value to false */
$update = false;

/* Parse data from GitHub hook payload */
$payload = json_decode($_POST['payload']);

/* When merging and pushing to GitHub, the commits array will be empty. */
if (empty($payload->commits)){

  /* In this case there is no way to know what branch was pushed to, so we will do an update. */
  $update = true;

} else {

  /* Loop through commits */
  foreach ($payload->commits as $commit) {

    /* Set the branch this commit is for */
    $branch = $commit->branch;

    /* Check if this branch matches our defined branch to deploy */
    if ($branch === $branch_to_deploy || isset($commit->branches) && in_array($branch_to_deploy, $commit->branches)) {

      /* Because this is a match we will do an update (and break out of the foreach loop) */
      $update = true;
      break;

    }

  } /* End loop through commits */

}

/* Check if there is a valid update */
if ($update) {

  /* Do a git checkout to the web root */
  exec('cd ' . $hidden_repo_dir . ' && ' . $git_bin_path  . ' fetch');
  exec('cd ' . $hidden_repo_dir . ' && GIT_WORK_TREE=' . $app_root_dir . ' ' . $git_bin_path  . ' checkout -f ' . $branch_to_deploy);

  /* Should we run composer install? */
  if ($run_composer) {

      /* Run composer install */
      shell_exec($composer_bin_path . ' install -d ' . $app_root_dir);

  }

  /* Should we run custom commands? */
  if ($run_custom_commands) {

    /* Run custom commands on shell */
    shell_exec($custom_commands);

  }

  /* Retrieve the commit hash */
  $commit_hash = shell_exec('cd ' . $hidden_repo_dir . ' && ' . $git_bin_path  . ' rev-parse --short ' . $branch_to_deploy);

  /* Log the result of the commit */
  file_put_contents($hidden_repo_dir . '/deploy.log', date('Y-m-d h:i:s a') . " Deployed Branch: " .  $branch . " Commit: " . $commit_hash, FILE_APPEND);

}
