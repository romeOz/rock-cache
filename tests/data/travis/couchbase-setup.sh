#!/bin/sh

if (php --version | grep -i HipHop > /dev/null); then
    echo "Skipping Couchbase on HHVM"
    exit 0
fi

# Download and uncompress Couchbase Server
#http://packages.couchbase.com/releases/4.0.0/couchbase-server-community_4.0.0-ubuntu12.04_amd64.deb
#sudo dpkg -i couchbase-server-community_4.0.0-ubuntu12.04_amd64.deb
wget http://packages.couchbase.com/releases/3.0.1/couchbase-server-community_3.0.1-ubuntu12.04_amd64.deb
dpkg -i couchbase-server-community_3.0.1-ubuntu12.04_amd64.deb

pecl channel-update pecl.php.net

# install this version
VERSION=2.0.7

pecl install -f couchbase-${VERSION}

#echo "extension = couchbase.so" >> /etc/php5/fpm/conf.d/couchbase.ini
#echo "extension = couchbase.so" >> /etc/php5/cli/conf.d/couchbase.ini
echo "extension = couchbase.so" >> ~/.phpenv/versions/$(phpenv version-name)/etc/php.ini

# Create test bucket
/opt/couchbase/bin/couchbase-cli bucket-create -c 127.0.0.1:8091 --bucket=default --bucket-type=memcached --bucket-ramsize=64 --enable-flush=1 -u demo -p demo