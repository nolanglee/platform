<?php

use Phinx\Migration\AbstractMigration;

class AddLayersOauthScope extends AbstractMigration
{

    /**
     * Migrate Up.
     */
    public function up()
    {
        $this->execute("INSERT INTO oauth_scopes (id, description) VALUES ('layers', 'layers')");
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $this->execute("DELETE FROM oauth_scopes WHERE id = 'layers'");
    }
}
