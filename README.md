# PoloniBox

Easily check the legitimacy of [Poloniex][] trollbox users and prove the existence of conversations, avoiding scammers to gain reputation.

[![Unmaintained](https://img.shields.io/maintenance/no/2015.svg)]()

[Poloniex]: https://poloniex.com

## Project structure

The project is split into 3 parts:

- __website__ is the frontend for presenting data to the users
  - __website/cronJobs.txt__ contains information about the cron jobs which have to be set up
- __server__ should be hosted 24/7 in order to keep logging messages
- __poloniex-dump-decoder__ can be used to sync the local MySQL DB with Poloniex's database by using an official dump file
