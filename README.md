# Sync directory contents over HTTP using PHP

Use these classes to recursively sync the contents of two folders on different servers. The source must have
a web server although the directory being synced does not have to be web accessible. The client initiates the
connection and can be either another web server or a command line script.


## Install

If using Composer, add `"outlandish/sync":"1.*@dev"` to your requirements.

Otherwise, just download and `require` the classes as normal.


## How it works

1. Client collects list of existing files in destination folder (and subfolders), with size and modified dates
2. Client POSTs list to the server
3. Server gets list of files in source folder on server and compares this with list of files from client
4. Server returns list of new or modified files present on server
5. Client requests contents of each new or modified file and saves it to destination folder
6. Client sets last modified time of file to match server

No attempt is made to send diffs; this is not rsync. Symlinks are not explicitly supported. All communication
is via JSON data in the request/response body.

## Example

On the server, e.g. `example.com/remote.php`:

```php
require_once 'vendor/autoload.php'; //or include AbstractSync.php and Server.php

const SECRET = '5ecR3t'; //make this long and complicated
const PATH = '/path/to/source'; //sync all files and folders below this path

$server = new \Outlandish\Sync\Server(SECRET, PATH);
$server->run(); //process the request
```

On the client(s):

```php
require_once 'vendor/autoload.php';

const SECRET = '5ecR3t'; //this must match the secret key on the server
const PATH = '/path/to/destination'; //target for files synced from server

$client = new \Outlandish\Sync\Client(SECRET, PATH);
$client->run('http://example.com/remote.php'); //connect to server and start sync
```

## FAQ

### Why not just use rsync?

Sometimes you need code to be portable across a range of hosting environments so you can't rely on rsync, scp or 
other external dependencies.
