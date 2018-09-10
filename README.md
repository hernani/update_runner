# Update Runner Drupal module
This module can be used to run update processes whenever it detects a core or contrib module update is available for your site.

The module depends on the core updater module to detect available updates and schedule automated jobs to be run when possible. These jobs are associated with processor plugins configured in your site to perform customizable update actions.

The default processor plugins present in the module push a code update to a remote code repository with information about the update available. This is only used to perform a push in the repository that can trigger a CI pipeline build job and therefore a new build based on the need of an automatic update.

Available processors:
- Github push
- Bitbucket push

The module is currently in an early development phase.


