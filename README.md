[![Build Status](https://travis-ci.org/vedmaka/mediawiki-extension-SphinxStore.svg?branch=master)](https://travis-ci.org/vedmaka/mediawiki-extension-SphinxStore)

# Intro

This extension is in development stage. SphinxStore allows to combine semantic
property-value search with full-text stemmed Sphinx search for text-based properties.

# Requirements

- Sphinx version 2.2.+
- PHP 5.6+
- Mediawiki 1.25+

# How to obtain 2.2 Sphinx on Ubuntu

```bash
$ add-apt-repository ppa:builds/sphinxsearch-rel22
$ apt-get update
$ apt-get install sphinxsearch
```