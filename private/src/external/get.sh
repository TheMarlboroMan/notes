#!/bin/bash
#set -x

function move {

	if [ -d ./$1 ]
	then
		rm -rf ./$1
	fi
	mv $2/$1 ./$1
}

repos="php-router php-orm php-request php-pattern-matcher php-log"
for repo in $repos
do
	if [ -d $repo ]
	then
		rm -rf $repo
	fi
	git clone https://github.com/themarlboroman/$repo
done

move log php-log/src
move sorm php-orm/src
move tools php-pattern-matcher/lib/src
move request php-request/lib/src
move srouter php-router/src

for repo in $repos
do
	rm -rf $repo
done

echo "done"

