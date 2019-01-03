<?php

namespace Friendica\Directory\Core\Console;

use Asika\SimpleConsole\CommandArgsException;
use Asika\SimpleConsole\Console;
use dba;
use Friendica\Directory\App;
use Net_Ping;

require_once 'include/dba.php';
require_once 'include/site-health.php';

/**
 * @brief Probe a single site
 *
 * License: AGPLv3 or later, same as Friendica
 *
 * @author Hypolite Petovan <mrpetovan@gmail.com>
 */
class Probe extends Console
{
	protected $helpOptions = ['h', 'help', '?'];

	protected function getHelp()
	{
		$help = <<<HELP
console globalcommunityblock - Block remote profile from interacting with this node
Usage
	bin/console globalcommunityblock <profile_url> [-h|--help|-?] [-v]

Description
	Blocks an account in such a way that no postings or comments this account writes are accepted to this node.

Options
    -h|--help|-? Show help information
    -v           Show more debug information.
HELP;
		return $help;
	}

	protected function doExecute()
	{
		global $db, $a;

        $a = new App();

		if ($this->getOption('v')) {
			$this->out('Class: ' . __CLASS__);
			$this->out('Arguments: ' . var_export($this->args, true));
			$this->out('Options: ' . var_export($this->options, true));
		}

		require_once '.htconfig.php';
        $db = new dba($db_host, $db_user, $db_pass, $db_data);
        unset($db_host, $db_user, $db_pass, $db_data);

        if ($this->getOption('all')) {
            $sites = q('SELECT * FROM `site-health` WHERE `health_score` >= 0');
            if (is_bool($sites)) {
                throw new \RuntimeException('SQL Error');
            } elseif (!count($sites)) {
                throw new \RuntimeException('No sites to probe ' . intval($this->getArgument(0)));
            } else {
                foreach($sites as $site) {
                    $this->out('Running probe for site ID ' . $site['id']);
                    run_site_probe($site['id'], $site);
                }
            }

            return 0;
        }

        if (count($this->args) == 0) {
			$this->out($this->getHelp());
			return 0;
		}

		if (count($this->args) > 1) {
			throw new CommandArgsException('Too many arguments');
		}

        if (is_numeric($this->getArgument(0))) {
            $site_health = q('SELECT * FROM `site-health` WHERE `id` = %u LIMIT 1', intval($this->getArgument(0)));

            if (is_bool($site_health)) {
                throw new \RuntimeException('SQL Error');
            } elseif (!count($site_health)) {
                throw new \RuntimeException('Unknown site with ID ' . intval($this->getArgument(0)));
            } else {
                $site_health = $site_health[0];
            }
        } else {
            $site_health = q('SELECT * FROM `site-health` WHERE `base_url` LIKE "%%%s%%" OR `effective_base_url` LIKE "%%%s%%" LIMIT 1',
                $this->getArgument(0), $this->getArgument(0));

            if (is_bool($site_health)) {
                throw new \RuntimeException('SQL Error');
            } elseif (!count($site_health)) {
                throw new \RuntimeException('Unknown site with base URL ' . $this->getArgument(0));
            } else {
                $site_health = $site_health[0];
            }
        }

        $this->out(var_export($site_health, true));

        $this->out('Running probe for site ID ' . $site_health['id']);
        run_site_probe($site_health['id'], $site_health);

        $this->out(var_export($site_health, true));

		return 0;
	}
}
