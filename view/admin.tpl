<div class="flagged-wrapper">
  <h1>Flagged entries</h1>
  <div class="flagged-entries">$flagged</div>
</div>

<div class="maintenance-wrapper">
  <h1>Maintenance</h1>
  <p>
    <strong>Current maintenance backlog: $maintenance_backlog</strong><br>
    <i>$maintenance_size</i>
  </p>
</div>

<div class="pulling-wrapper">
  <h1>Pulling</h1>
  <p>
    <strong>Current pulling backlog: $pulling_backlog</strong><br>
  </p>
</div>

<div class="import-wrapper">
  <h1>Import tools</h1>
  <h2>Mirror a directory</h2>
  <p>This is very slow, faster would be to use pull targets as that is multi-threaded.</p>
  <form method="POST">
    <label>Extract URL's:</label>
    <input type="text" name="dir_import_url" value="http://dir.friendica.com">
    <input type="hidden" name="dir_page" value="0">
    <input type="submit" value="Execute">
  </form>
  <br>
  <form method="POST">
    <label>Batch submit from file: $present</label>
    <input type="submit" name="batch_submit" value="Run batch">
  </form>
  <h2>Manual submit</h2>
  <form method="POST">
    <input type="text" name="submit_url" placeholder="Profile url" size="35" />
    <input type="submit" value="Submit">
  </form>
</div>