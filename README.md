# notes

## install

- setup the dependencies (private/src/external/get.sh)
- dump the whole project into your web hosting.
- delete README.md, .gitignore and .git if needed.
- create a database, note the host username and pass.
- dump the private/data/db.txt file into your database.
- copy instance/conf/config.dist.json into instance/conf/config.json
- setup config.json as needed.

## directory structure

- public: public files (login)
- private: private files, controllers, dependencies...
- instance: whatever it is that is specific to your install (configuration, logs...)

## on licensing and dependencies

This project is meant to be put in the public domain. All dependencies are myself and use the MIT license. If something looks off, drop me a line.
