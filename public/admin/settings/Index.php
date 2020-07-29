<?php

/**
 * This file is part of DSJAS
 * Written and maintained by the DSJAS project.
 * 
 * Copyright (C) 2020 - Ethan Marshall
 * 
 * DSJAS is free software which is licensed and distributed under
 * the terms of the MIT software licence.
 * Exact terms can be found in the LICENCE file.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * above mentioned licence for specific details.
 */

require "../AdminBootstrap.php";

require ABSPATH . INC . "Update.php";
require ABSPATH . INC . "csrf.php";
require ABSPATH . INC . "Administration.php";

require_once ABSPATH . INC . "Customization.php";


$config = new Configuration(true, false, false, false);

$db_hostname = $configuration["server_hostname"];
$db_dbname = $configuration["database_name"];
$db_username = $configuration["username"];
$db_password = $configuration["password"];


if (isset($_POST["doSave"])) {
    $csrf = verifyCSRFToken(getCSRFSubmission());

    if (!$csrf) {
        die(getCSRFFailedError());
    }

    $config->setKey(ID_GLOBAL_CONFIG, "database", "running_without_database", $_POST["noDB"]);
    $config->setKey(ID_GLOBAL_CONFIG, "database", "server_hostname", $_POST["dbHost"]);
    $config->setKey(ID_GLOBAL_CONFIG, "database", "database_name", $_POST["dbDatabase"]);
    $config->setKey(ID_GLOBAL_CONFIG, "database", "username", $_POST["dbUser"]);
    $config->setKey(ID_GLOBAL_CONFIG, "database", "password", $_POST["dbPass"]);

    $config->setKey(ID_GLOBAL_CONFIG, "customization", "bank_name", $_POST["bankName"]);
    $config->setKey(ID_GLOBAL_CONFIG, "customization", "bank_domain", $_POST["bankURL"]);

    $config->setKey(ID_GLOBAL_CONFIG, "settings", "allow_access_to_admin", $_POST["adminAccess"]);

    die(); // Don't return the panel for the POST request
} else if (isset($_POST["doResetInstall"])) {
    installReset($config);
    die();
} else if (isset($_POST["doResetFactory"])) {
    factoryReset($config);
    die();
}

regenerateCSRF();

$noDatabase = $config->getKey(ID_GLOBAL_CONFIG, "database", "running_without_database");

?>

<html>
<?php require ABSPATH . INC . "components/AdminSidebar.php"; ?>

<p style="display: none" id="csrfToken"><?php echo (getCSRFToken()); ?></p>

<div class="content container-fluid" id="content">
    <div class="alert alert-warning d-lg-none">
        <p><strong>Warning:</strong> The admin dashboard is not designed for smaller screens, and some functionality may be missing or limited.</p>
    </div>

    <?php require ABSPATH . INC . "components/AdminSettingsNav.php";

    if (isset($_GET["success"])) { ?>
        <div class="alert alert-success alert-dismissible fade show">
            <strong>Settings saved</strong> Your settings changes were saved.
            <button type="button" class="close" data-dismiss="alert">
                <span>&times;</span>
            </button>
        </div>
    <?php } else if (isset($_GET["error"])) { ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <strong>Error saving settings</strong> There was an error while attempting to save your settings. Your changes were not saved.
            <button type="button" class="close" data-dismiss="alert">
                <span>&times;</span>
            </button>
        </div>
    <?php } else if (isset($_GET["factorySuccess"])) { ?>
        <div class="alert alert-info alert-dismissible fade show">
            <strong>Factory reset complete</strong> The site has been reset to default factory settings.
            <button type="button" class="close" data-dismiss="alert">
                <span>&times;</span>
            </button>
        </div>
    <?php }

    ?>

    <div class="modal" id="installConfirmation" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="installModalTitle">Please confirm</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p>Resetting the installation process can cause data to be lost and will reset your configuration.
                        <strong>Please confirm you wish to continue.</strong></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger" onclick="resetInstall()">Confirm</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal" id="resetConfirmation" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="installModalTitle">Please confirm</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p>Resetting to factory settings will revert all your changes to the site and all settings to their factory default.
                        This may cause issues with your database or user accounts.
                        <strong>Please confirm you wish to continue.</strong></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger" onclick="resetFactory()">Confirm</button>
                </div>
            </div>
        </div>
    </div>

    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
        <h1 class="admin-header col col-offset-6">DSJAS Settings</h1>
    </div>

    <div class="card bg-light admin-panel">
        <div class="card-header d-flex justify-content-between">
            <h3>Site appearance settings</h3>
        </div>

        <div class="card-body">
            <form>
                <div class="form-group">
                    <label for="bankName"><strong>Bank name:</strong></label>
                    <input class="form-control" type="text" id="bankName" value="<?php echo (getCurrentBankName()); ?>">
                    <small class="form-text text-muted">This is the name which will be visible as the name of the bank</small>
                </div>

                <div class="form-group">
                    <label for="bankURL"><strong>Bank URL:</strong></label>
                    <input class="form-control" type="text" id="bankURL" value="<?php echo (getCurrentBankURL()); ?>">
                    <small class="form-text text-muted">This is the URL that you will access the bank through in your web browser</small>
                </div>
            </form>
        </div>
    </div>

    <div class="card bg-light admin-panel">
        <div class="card-header d-flex justify-content-between">
            <h3>Security settings</h3>
        </div>

        <div class="card-body">
            <form>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="adminAccess" checked>
                    <label class="form-check-label" for="adminAccess">
                        Allow access to admin panel
                    </label>
                    <small class="form-text text-muted">Disabling access to the admin panel will prevent administrator logins and all admin pages.
                        You may wish to disable this during a live site usage or if you do not plan on changing settings mid-use.
                        However, you will be unable to access this page again to disable the setting and will need to edit the configuration on disk to revert this.
                    </small>
                </div>
            </form>
        </div>
    </div>

    <div class="card bg-light admin-panel">
        <div class="card-header d-flex justify-content-between">
            <h3>Database settings</h3>
        </div>

        <div class="card-body">
            <div class="form-group">
                <label for="dbHostname"><strong>Database hostname:</strong></label>
                <input class="form-control" type="text" id="dbHostname" value="<?php echo ($db_hostname); ?>">
            </div>

            <div class="form-group">
                <label for="dbDatabase"><strong>Database name:</strong></label>
                <input class="form-control" type="text" id="dbDatabase" value="<?php echo ($db_dbname); ?>">
            </div>

            <div class="form-group">
                <label for="dbUsername"><strong>Username:</strong></label>
                <input class="form-control" type="text" id="dbUsername" value="<?php echo ($db_username); ?>">
            </div>

            <div class="form-group">
                <label for="dbPassword"><strong>Password:</strong></label>
                <input class="form-control" type="password" id="dbPassword" value="<?php echo ($db_password); ?>">
                <small class="form-text text-muted">
                    If you ever forget your password, it is still stored in the site configuration
                </small>
            </div>

            <hr>

            <div class="form-check">
                <input class="form-check-input" type="checkbox" id="noDatabase" <?php if ($noDatabase) {
                                                                                    echo ("checked");
                                                                                } ?>>
                <label class="form-check-label" for="noDatabase">
                    Run without a database <i>(not recommended)</i>
                </label>
                <small class="form-text text-muted">
                    Check this option to force the site to attempt to run without a database. Logins and user accounts will be unavailable.
                    All above options will be ignored if this is enabled.
                </small>
            </div>
        </div>
    </div>

    <div class="card bg-light admin-panel">
        <div class="card-header d-flex justify-content-between">
            <h3>Installation settings</h3>
        </div>

        <div class="card-body">
            <button type="button" class="btn btn-warning" data-toggle="modal" data-target="#installConfirmation">Reset installation process</button>
            <button type="button" class="btn btn-danger" data-toggle="modal" data-target="#resetConfirmation">Reset to factory settings</button>
        </div>
    </div>

    <div class="card bg-light admin-panel">
        <div class="card-body">
            <button onclick="generalSaveSettings()" class="btn btn-primary">
                Save
                <span id="saveProgress" class="spinner-border spinner-border-sm" style="display: none"></span>
            </button>
            <button onclick="discardChanges()" class="btn btn-danger">Discard changes</button>
        </div>
    </div>
</div>

</html>