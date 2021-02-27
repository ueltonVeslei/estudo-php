# -*- coding: utf8 -*-

import os
import time
import ansible.inventory
import ansible.runner
import ansible.errors
import getpass
import socket
import logging
import MySQLdb as mdb


class UpdateProd(object):
    """New-style Class to do everything needed to move content from staging to production or at least
    orchestrate it"""
    def __init__(self, main_host, loglevel="info"):
        self._main_host = main_host
        self._backends = []
        self._frontends = []
        self._db = None
        self._remote_user = None
        self._local_user = None
        self._remote_home = None
        self._local_home = None
        self._src = None
        self._dest = None
        self._pattern = "127.0.0.1"
        self._serverfacts = {}
        self.inventory = None

        loglevel = loglevel.upper()
        numeric_level = getattr(logging, loglevel, None)
        if not isinstance(numeric_level, int):
            raise ValueError('Invalid log level: %s' % loglevel)
        logging.basicConfig(level=numeric_level)

        # Just _main_host will be included but it is ok for now
        self.build_inventory()

    @property
    def user(self):
        if self.pattern == "127.0.0.1":
            if self._local_user is None:
                self._local_user = getpass.getuser()
            return self._local_user

        if self._remote_user is None:
            self._remote_user = getpass.getuser()
        return self._remote_user

    @user.setter
    def user(self, username):
        if self.db is None:
            self.db = username
        if self.pattern == "127.0.0.1":
            self._local_user = username
        else:
            self._remote_user = username

    @property
    def home(self):
        if self.pattern == "127.0.0.1":
            if self._local_home is None:
                self._local_home = self.get_local_home()
            return self._local_home

        if self._remote_home is None:
            self._remote_home = self.get_remote_home(self.user)
        return self._remote_home

    @home.setter
    def home(self, home):
        if self.pattern == "127.0.0.1":
            self._local_home = home
        else:
            self._remote_home = home

    @property
    def main_host(self):
        return self._main_host

    @main_host.setter
    def main_host(self, host):
        self.pattern = host
        self._main_host = host

    @property
    def backends(self):
        return self._backends

    @backends.setter
    def backends(self, hosts):
        self._backends = hosts

    @property
    def frontends(self):
        return self._frontends

    @frontends.setter
    def frontends(self, hosts):
        self._frontends = hosts

    @property
    def src(self):
        return self._src

    @src.setter
    def src(self, src):
        if self.dest is None:
            self.dest = src
        self._src = src

    @property
    def dest(self):
        return self._dest

    @dest.setter
    def dest(self, dest):
        self._dest = dest

    @property
    def db(self):
        if self._db is None:
            self._db = self.user
        return self._db

    @db.setter
    def db(self, db):
        self._db = db

    @property
    def pattern(self):
        return self._pattern

    @pattern.setter
    def pattern(self, pattern):
        if pattern == "localhost" or pattern == self.localhost:
            pattern = "127.0.0.1"
        self._pattern = pattern

    @property
    def localhost(self):
        """Get information about the localhost fullname."""
        if socket.gethostname().find('.') >= 0:
            host = socket.gethostname()
        else:
            host = socket.gethostbyaddr(socket.gethostname())[0]
        return host

    @property
    def serverfacts(self):
        return self._serverfacts

    @serverfacts.setter
    def serverfacts(self, host):
        """Use ansible module 'setup' to take server facts from local or remote. Experimental status"""
        if not self._check_inventory():
            self.serverfacts = None
        else:
            logging.info("Getting facts from %s" % host)
            facts = ansible.runner.Runner(
                module_name="setup",
                remote_user=self.user,
                pattern=host,
                inventory=self.inventory,
                forks=1
            ).run()

            self._ansible_feedback(facts, priority=1)
            try:
                received_facts = facts['contacted'][self._pattern]
            except KeyError:
                received_facts = None

            if received_facts:
                self._serverfacts = received_facts

    def build_inventory(self):
        """Add one or more lists of hosts to Ansible inventory"""
        hosts = self.frontends + self.backends
        hosts.append(self.main_host)
        # remove duplicates, the order is not important for inventories
        dedup = list(set(hosts))
        self.inventory = ansible.inventory.Inventory(dedup)
        return True

    def set_remote(self, pattern=None):
        """Reset the pattern to the main_host"""
        if pattern:
            self.pattern = pattern
        else:
            self.pattern = self.main_host

        return self.pattern

    def set_local(self):
        """Reset the _pattern to localhost, in other words, the hosts from where ansible is run.
        This allow us to send local actions in the staging environment"""
        self.pattern = "127.0.0.1"

    def reindexer(self, index='cataloginventory_stock'):
        """Reindex either on local or remote hosts"""
        logging.info('Reindexing %s on %s' % (index, self.pattern))
        if self.smart_exec("indexer.php", options="--reindex %s" % index, priority=1):
            return True

        logging.error("Something wrong happened while reindexing %s on %s" % (index, self.pattern))
        return False

    def backup(self, datafile):
        """Database dumps from selected tables using the shell script backupdb.sh located in the shell directory
        of the Magento installation. The shell script should be always present for update_prod.py to run"""

        fullpath = self.home + "/" + datafile
        logging.info("Backing up the database %s in %s" % (self.db, fullpath))

        if self.smart_exec("backupdb.sh", "-d %s -p %s" % (self.db, fullpath)):
            return fullpath

        logging.critical("Something is wrong with the backup of db %s on %s" % (self.db, self.pattern))
        return False

    def smart_exec(self, script, options=None, priority=2):
        """Smart function that can recognize files by extension to launch to launch the exec by itself.
        When the function detects a relative path it asumes the script is located inside the Magento's
        shell directory. It is possible to send script options and to have the execution priority defined.
        Ex: If the priority is 1 a failure doesn't stop the process but still logged."""
        if not self._check_inventory():
            return False

        shell_path = self.home + "/current/web/shell/" + script
        if script[:1] != "/" and not (' ' in shell_path):
            fullpath = shell_path

            extension = self._splitext(fullpath)
            if extension == ".php":
                runner = "/usr/bin/php %s" % fullpath
            elif extension == ".sh":
                runner = "/bin/bash %s" % fullpath
            elif extension == ".py":
                runner = "/usr/bin/python %s" % fullpath
            elif extension == ".rb":
                runner = "/usr/bin/ruby %s" % fullpath
            else:
                # Try to exec the file directly
                runner = fullpath
        else:
            runner = script

        if options is not None:
            runner = runner + " " + options

        logging.info("Executing %s on %s" % (runner, self.pattern))
        exec_command = ansible.runner.Runner(
            module_name='shell',
            module_args='%s' % runner,
            remote_user=self.user,
            pattern=self.pattern,
            inventory=self.inventory,
            forks=10
        ).run()

        if self._ansible_feedback(exec_command, priority=priority):
            return True
        return False

    def sync_dirs(self, rsync_opts=""):
        """Sync local to local or local to remote directories. The function can receive extra rsync opts and
        has already some default setup. The content of a dir always goes to another, not the dir itself, so
        if not present, a / will be added at the end of the source."""
        if self.src is None:
            logging.critical("At least a source path must be set to sync directories")
            return False
        elif self.dest == self.src and self.pattern == "127.0.0.1":
            logging.critical("The destination path must be set for local syncs")
            return False

        if self.src[1:] != "/":
            self.src += "/"

        if rsync_opts != "":
            rsync_opts = "," + rsync_opts

        logging.info("Syncing %s to %s on %s" % (self.src, self.dest, self.pattern))
        sync_dirs = ansible.runner.Runner(
            module_name="synchronize",
            module_args="src=%s dest=%s archive=no recursive=yes times=no owner=no perms=no delete=yes rsync_opts=--chmod=a+rwx%s" \
                % (self.src, self.dest, rsync_opts),
            pattern=self.pattern,
            remote_user=self.user,
            inventory=self.inventory,
            forks=4
        ).run()

        if self._ansible_feedback(sync_dirs):
            return True

        logging.critical("Nothing done when syncing directories. Something should be wrong with the code")
        return False

    def copy_file(self, src=None, dest=None):
        """Copy a local file to a remote server"""
        if src:
            self.src = src

        if dest:
            self.dest = dest

        if self.src is None:
            logging.critical("At least a source path must be set to copy files")
            return False

        if self.dest == self.src and self.pattern == "127.0.0.1":
            logging.critical("The destination path must be set to copy a file from and to %s" % self.localhost)
            return False

        logging.info("Copying the file %s to remote %s" % (self.src, self.dest))
        copyfile = ansible.runner.Runner(
            module_name='copy',
            module_args='src=%s dest=%s mode=0644 force=yes' % (self.src, self.dest),
            pattern=self.pattern,
            remote_user=self.user,
            inventory=self.inventory,
            forks=4
        ).run()

        if self._ansible_feedback(copyfile):
            return self.dest
        return False

    def mysql_import(self):
        """Import a MySQL dump to a remote database. The function checks at first if the needed python module for
        MySQL is installed. If the option restore is set to True, the backup from the home directory is used."""
        self.is_installed("MySQL-python")

        if not self.check_file():
            logging.critical("The remote file %s is not found on %s" % (self.src, self.pattern))
            return False

        logging.info("Importing %s to the MySQL database in %s" % (self.src, self.pattern))
        import_dump = ansible.runner.Runner(
            module_name='mysql_db',
            module_args='name=%s state=import target=%s' % (self.db, self.src),
            pattern=self.pattern,
            remote_user=self.user,
            inventory=self.inventory,
            forks=1
        ).run()

        if self._ansible_feedback(import_dump):
            return True
        return False

    def mysql_truncate(self, table):
        """
        Truncate a MySQL table in localhost. The database is taken from self.db and
        the credentials are read from local user ~/.my.cnf.
        """
        con = mdb.connect(host="localhost", db=self.db, read_default_file="~/.my.cnf")

        try:
            cur = con.cursor()
            cur.execute("TRUNCATE %s" % table)
            con.commit()
        except con.ProgrammingError, e:
            logging.critical(str(e))
            return False
        except:
            logging.critical("Something went wrong truncating table %s" % table)
            return False
        else:
            return True

    def mysql_count(self, table):
        """
        Count the rows from a table and returns the value. The database is taken from
        self.db and the credentials are read from the local user ~/.my.cnf.
        """
        con = mdb.connect(host="localhost", db=self.db, read_default_file="~/.my.cnf")

        try:
            with con:
                cur = con.cursor()
                cur.execute("SELECT count(*) as total FROM %s" % table)
                result = cur.fetchone()
                total_rows = result[0]
        except con.ProgrammingError, e:
            logging.critical(str(e))
            return False
        except:
            logging.critical("Something went wrong counting rows in table %s" % table)
            return False
        else:
            return total_rows

    def check_file(self, path=None, state="file", delete=False):
        """Checks if a local or remote file exists."""
        if path is None and self.src is not None:
            path = self.src
        elif path is None and self.src is None:
            logging.critical("The source path is not set!")
            return False

        if delete is True:
            state = "absent"
            logging.info("Deleting file %s in %s" % (path, self.pattern))

        if state == "file" or state == "directory":
            logging.info("Checking if %s %s exists in %s" % (state, path, self.pattern))

        checked_file = ansible.runner.Runner(
            module_name='file',
            module_args='path=%s state=%s' % (path, state),
            pattern=self.pattern,
            remote_user=self.user,
            inventory=self.inventory,
            forks=10
        ).run()

        if self._ansible_feedback(checked_file, priority=3):
            return True
        return False

    def is_installed(self, package="MySQL-python"):
        """Checks if an rpm package is installed in local or remote host."""
        logging.info('Checking if %s is available on %s' % (package, self.pattern))
        yum_package = ansible.runner.Runner(
            module_name='yum',
            module_args='name=%s state=present' % package,
            pattern=self.pattern,
            remote_user=self.user,
            inventory=self.inventory,
            forks=10
        ).run()

        if self._ansible_feedback(yum_package):
            return True
        return False

    def delete_cache(self, cache="redis"):
        """Delete some Magento caches: redis, varnish or ezoom. For redis if a number is set for self.db only that
        database is deleted."""
        if cache is "redis":
            logging.info("Flushing all the redis cache")
            if self.smart_exec('/usr/bin/redis-cli flushall', priority=1):
                return True

        if cache is "varnish":
            logging.info("Deleting varnish cache")
            if self.smart_exec("varnishadm.php", options="-- ban.url .", priority=1):
                return True

        if cache is "ezoom":
            logging.info("Deleting ezoom cache")
            varpath = self.home + '/shared/web/var'
            if self.smart_exec("/bin/mv %s/zoom %s/zoom-x && sleep 5 && /bin/rm -rf %s/zoom-x" % (varpath, varpath, varpath), priority=1):
                return True

        logging.warning("The %s cache cannot be deleted from %s" % (cache, self.pattern))
        return False

    def clean_dir(self, directory, sleep=2):
        logging.info("Cleaning directory %s on %s" % (directory, self.pattern))
        if self.smart_exec("mkdir %s-back && mv %s/* %s-back/ && sleep %d && rm -rf %s-back" % (directory, directory, directory, sleep, directory)):
            return True
        return False

    def send_message(self, msg, to, notifier="slack", token=None, subject=None, sender=None, domain="onestic.slack.com"):
        """Sends a message to different services: slack, email, pushover or osx voice. The pushover version is
        not yet implemented."""

        if notifier == "slack":
            # from v1.6
            logging.info("Sending message to slack channel %s" % to)
            send_slack = ansible.runner.Runner(
                module_name="slack",
                module_args="domain=%s token=\"%s\" msg=\"%s\" channel=\"%s\"" % (domain, token, msg, to),
                pattern="127.0.0.1",
                remote_user=self.user,
                inventory=self.inventory,
                forks=1
            ).run()

            if self._ansible_feedback(send_slack, priority=1):
                return True

        if notifier == "email":
            if sender is None:
                sender = self.user + "@" + self.localhost
            logging.info("Sending message with subject \"%s\" to %s" % (subject, to))
            send_email = ansible.runner.Runner(
                module_name="mail",
                module_args="subject=\"%s\" body=\"%s\" to=\"%s\" from=\"%s\" charset=uft8" % (subject, msg, to, sender),
                pattern="127.0.0.1",
                remote_user=self.user,
                inventory=self.inventory,
                forks=1
            ).run()

            if self._ansible_feedback(send_email, priority=1):
                return True

        if notifier == "pushover":
            logging.error("Please, use the module Pushover from Onestic: https://bitbucket.org/onestic/pushover-python")
            return False

        if notifier == "voice":
            logging.info("Sending OSX message")
            talk = ansible.runner.Runner(
                module_name="osx_say",
                module_args="msg=\"%s\" voice=Zarvox" % msg,
                pattern="127.0.0.1",
                remote_user=self.user,
                inventory=self.inventory,
                forks=1
            ).run()

            if self._ansible_feedback(talk, priority=1):
                return True
        return False

    @staticmethod
    def wait(seconds):
        logging.info("Waiting for %d seconds" % seconds)
        time.sleep(seconds)

    @staticmethod
    def get_local_home():
        home = os.path.expanduser("~")
        return home

    @staticmethod
    def get_remote_home(username):
        remote_home = "~%s" % username
        return remote_home

    def _ansible_feedback(self, results, priority=2):
        """Function to have all the output of ansible processed. Supports multiple servers."""
        if results is None:
            logging.error('No hosts found to sync with the pattern %s' % self.pattern)
            raise ansible.errors.AnsibleError("provided hosts list is empty")

        for (hostname, result) in results['contacted'].items():
            if not 'failed' in result:
                try:
                    result["stderr"]
                except KeyError:
                    result["stderr"] = None
                if result["stderr"]:
                    logging.warning("KO: %s" % result["stderr"])
                    if priority > 1:
                        return False
                logging.info("OK: %s" % hostname)
            if 'failed' in result:
                logging.warning("KO: %s" % hostname)
                print results
                return False

        for (hostname, result) in results['dark'].items():
            logging.error("%s is DOWN: %s" % (hostname, result['msg']))
            # if priority is bigger than 1 it stops (default: 2)
            if priority > 1:
                return False
        return True

    def _splitext(self, path):
        return os.path.splitext(path)[1]

    def _check_inventory(self):
        """To be sure that an ansible inventory is set."""
        if len(self.inventory.list_hosts()) == 0:
            logging.critical("The inventory list is empty")
            raise ansible.errors.AnsibleError("Provided hosts list is empty")
        return True

    def _check_min_file_size(self, filename, minsize):
        size = os.stat(filename)
        if size.st_size < minsize:
            logging.critical("The file %s is too small (%d KB)" % (filename, 1024 * size.st_size))
            return False
        return True
