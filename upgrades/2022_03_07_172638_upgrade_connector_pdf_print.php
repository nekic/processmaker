<?php

use ProcessMaker\Upgrades\UpgradeMigration as Upgrade;

class UpgradeConnectorPdfPrint extends Upgrade
{
    /**
     * The version of ProcessMaker being upgraded *to*
     *
     * @var string example: 4.2.28
     */
    public $to = '4.2.22';

    /**
     * Upgrade migration cannot be skipped if the pre-upgrade checks fail
     *
     * @var bool
     */
    public $required = false;

    /**
     * Run any validations/pre-run checks to ensure the environment, settings,
     * packages installed, etc. are right correct to run this upgrade.
     *
     * There is no need to check against the version(s) as the upgrade
     * migrator will do this automatically and fail if the correct
     * version(s) are not present.
     *
     * Return false if the conditions are *NOT* correct and if this is not
     * a required upgrade, then it will be skipped. Otherwise this will
     * result in an exception being thrown and the upgrades will be
     * kept from continuing to run.
     *
     * Returning void or null is equivalent to return true, meaning the
     * checks were successful.
     *
     * @return void|bool
     */
    public function preflightChecks()
    {
        //
    }

    /**
     * Run the upgrade migrations.
     *
     * @return void
     */
    public function up()
    {
        //
    }

    /**
     * Reverse the upgrade migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}

