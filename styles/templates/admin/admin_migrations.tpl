<h1>{L_MIGRATIONS_STATUS}</h1>

<table class="forumline" cellpadding="4" cellspacing="1" border="0" width="100%">
    <tr>
        <th class="thHead" colspan="2">{L_MIGRATIONS_DATABASE_INFO}</th>
    </tr>
    <tr>
        <td class="row1" width="35%"><b>{L_MIGRATIONS_DATABASE_NAME}:</b></td>
        <td class="row2">{SCHEMA_DATABASE_NAME}</td>
    </tr>
    <tr>
        <td class="row1"><b>{L_MIGRATIONS_DATABASE_TOTAL}:</b></td>
        <td class="row2">{SCHEMA_TABLE_COUNT}</td>
    </tr>
    <tr>
        <td class="row1"><b>{L_MIGRATIONS_DATABASE_SIZE}:</b></td>
        <td class="row2">{SCHEMA_SIZE_MB} MB</td>
    </tr>
    <tr>
        <td class="row1"><b>{L_LAST_UPDATED}:</b></td>
        <td class="row2">{CURRENT_TIME}</td>
    </tr>
</table>

<br/>

<table class="forumline" cellpadding="4" cellspacing="1" border="0" width="100%">
    <tr>
        <th class="thHead" colspan="2">{L_MIGRATIONS_STATUS}</th>
    </tr>
    <tr>
        <td class="row1" width="35%"><b>{L_MIGRATIONS_SYSTEM}:</b></td>
        <td class="row2">
            <!-- IF MIGRATION_TABLE_EXISTS -->
            <!-- IF SETUP_REQUIRES_SETUP -->
            <span style="color: orange; font-weight: bold;">⚠ {L_MIGRATIONS_NEEDS_SETUP}</span>
            <!-- ELSE -->
            <span style="color: green; font-weight: bold;">✓ {L_MIGRATIONS_ACTIVE}</span>
            <!-- ENDIF -->
            <!-- ELSE -->
            <span style="color: red; font-weight: bold;">✗ {L_MIGRATIONS_NOT_INITIALIZED}</span>
            <!-- ENDIF -->
        </td>
    </tr>
    <!-- IF SETUP_ACTION_REQUIRED -->
    <tr>
        <td class="row1"><b>Setup Status:</b></td>
        <td class="row2">
            <div
                style="background: #fff3cd; padding: 8px; border: 1px solid #ffeaa7; border-radius: 4px; margin: 4px 0;">
                <strong>Action Required:</strong> {SETUP_MESSAGE}<br>
                <!-- IF SETUP_INSTRUCTIONS -->
                <small><strong>Instructions:</strong> {SETUP_INSTRUCTIONS}</small><br>
                <!-- ENDIF -->
                <small><a href="#migration-setup-guide">See setup guide below</a></small>
            </div>
        </td>
    </tr>
    <!-- ENDIF -->
    <tr>
        <td class="row1"><b>Current Version:</b></td>
        <td class="row2">
            <!-- IF MIGRATION_CURRENT_VERSION -->
            {MIGRATION_CURRENT_VERSION}
            <!-- ELSE -->
            <em>No migrations applied</em>
            <!-- ENDIF -->
        </td>
    </tr>
    <tr>
        <td class="row1"><b>Applied Migrations:</b></td>
        <td class="row2">{MIGRATION_APPLIED_COUNT}</td>
    </tr>
    <tr>
        <td class="row1"><b>Pending Migrations:</b></td>
        <td class="row2">
            <!-- IF MIGRATION_PENDING_COUNT > 0 -->
            <span style="color: orange; font-weight: bold;">{MIGRATION_PENDING_COUNT} pending</span>
            <!-- ELSE -->
            <span style="color: green;">All up to date</span>
            <!-- ENDIF -->
        </td>
    </tr>
</table>

<!-- IF MIGRATION_APPLIED_COUNT > 0 -->
<br/>
<table class="forumline" cellpadding="4" cellspacing="1" border="0" width="100%">
    <tr>
        <th class="thHead" colspan="4">Applied Migrations</th>
    </tr>
    <tr>
        <td class="catHead" width="15%"><b>Version</b></td>
        <td class="catHead" width="35%"><b>Migration Name</b></td>
        <td class="catHead" width="25%"><b>Applied At</b></td>
        <td class="catHead" width="25%"><b>Completed At</b></td>
    </tr>
    <!-- BEGIN applied_migrations -->
    <tr>
        <td class="{applied_migrations.ROW_CLASS}">{applied_migrations.VERSION}</td>
        <td class="{applied_migrations.ROW_CLASS}">{applied_migrations.NAME}</td>
        <td class="{applied_migrations.ROW_CLASS}">{applied_migrations.START_TIME}</td>
        <td class="{applied_migrations.ROW_CLASS}">{applied_migrations.END_TIME}</td>
    </tr>
    <!-- END applied_migrations -->
</table>
<!-- ENDIF -->

<!-- IF MIGRATION_PENDING_COUNT > 0 -->
<br/>
<table class="forumline" cellpadding="4" cellspacing="1" border="0" width="100%">
    <tr>
        <th class="thHead" colspan="3">Pending Migrations</th>
    </tr>
    <tr>
        <td class="catHead" width="15%"><b>Version</b></td>
        <td class="catHead" width="35%"><b>Migration Name</b></td>
        <td class="catHead" width="50%"><b>File</b></td>
    </tr>
    <!-- BEGIN pending_migrations -->
    <tr>
        <td class="{pending_migrations.ROW_CLASS}">{pending_migrations.VERSION}</td>
        <td class="{pending_migrations.ROW_CLASS}">{pending_migrations.NAME}</td>
        <td class="{pending_migrations.ROW_CLASS}"><code>{pending_migrations.FILENAME}</code></td>
    </tr>
    <!-- END pending_migrations -->
</table>

<br/>
<div class="alert-warning"
     style="padding: 10px; background-color: #fff3cd; border: 1px solid #ffeaa7; border-radius: 4px;">
    <strong>⚠️ Pending Migrations Detected</strong><br/>
    There are {MIGRATION_PENDING_COUNT} migration(s) that need to be applied.
    Contact your system administrator to run:<br/>
    <code style="background: #f8f9fa; padding: 2px 4px;">php vendor/bin/phinx migrate</code>
</div>
<!-- ENDIF -->

<br/>
<div class="info-box" style="padding: 15px; background-color: #e9ecef; border-radius: 4px;">
    <h3>Migration Management</h3>
    <p>This panel provides read-only information about the database migration status.
        To manage migrations, use the command line interface:</p>

    <ul>
        <li><strong>Check status:</strong> <code>php vendor/bin/phinx status</code></li>
        <li><strong>Run migrations:</strong> <code>php vendor/bin/phinx migrate</code></li>
        <li><strong>Create new migration:</strong> <code>php vendor/bin/phinx create MigrationName</code></li>
        <li><strong>Rollback last migration:</strong> <code>php vendor/bin/phinx rollback</code></li>
    </ul>

    <p><strong>⚠️ Important:</strong> Always backup your database before running migrations in production!</p>
</div>

<!-- IF SETUP_REQUIRES_SETUP -->
<br/>
<div id="migration-setup-guide" class="setup-guide"
     style="padding: 15px; background-color: #f8d7da; border: 1px solid #f5c6cb; border-radius: 4px;">
    <h3 style="color: #721c24;">🔧 Migration Setup Required</h3>
    <p>Your installation has existing data but hasn't been set up for migrations yet. Follow these steps:</p>

    <h4>Step 1: Backup Your Database</h4>
    <pre style="background: #f8f9fa; padding: 10px; border-radius: 4px;">mysqldump -u username -p database_name > backup_$(date +%Y%m%d_%H%M%S).sql</pre>

    <h4>Step 2: Initialize Migration System</h4>
    <pre style="background: #f8f9fa; padding: 10px; border-radius: 4px;">php vendor/bin/phinx init</pre>

    <h4>Step 3: Mark Initial Migrations as Applied</h4>
    <pre style="background: #f8f9fa; padding: 10px; border-radius: 4px;">php vendor/bin/phinx migrate --fake --target=20250619000001
php vendor/bin/phinx migrate --fake --target=20250619000002</pre>

    <h4>Step 4: Verify Setup</h4>
    <pre style="background: #f8f9fa; padding: 10px; border-radius: 4px;">php vendor/bin/phinx status</pre>

    <p><strong>What this does:</strong> The <code>--fake</code> flag marks migrations as applied without actually
        running them,
        since your database already has the schema. This allows future migrations to work normally.</p>

    <p><strong>📖 Need help?</strong> See the complete guide in the
        <a href="https://github.com/torrentpier/torrentpier/blob/master/UPGRADE_GUIDE.md#migration-setup-for-existing-installations"
           target="_blank">UPGRADE_GUIDE.md</a></p>
</div>
<!-- ENDIF -->
