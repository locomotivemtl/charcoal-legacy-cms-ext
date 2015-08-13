Charcoal CMS Playground Extension
=================================

A CMS module extension that provides replacements to existing interfaces and new features to play with before maybe folding into `charcoal-legacy`.

## Installation

#### With Composer

```shell
$ composer require locomotivemtl/charcoal-cms-ext
```

```json
{
	"require": {
		"locomotivemtl/charcoal-cms-ext": "@dev"
	}
},
"repositories": [
	{
		"type": "vcs",
		"url": "https://github.com/locomotivemtl/charcoal-legacy-cms-ext"
	}
]
```

#### With SVN

```shell
$ cd www/modules
$ svn propset svn:externals . "cms-ext https://github.com/locomotivemtl/charcoal-legacy-cms-ext/trunk"
```

#### With Git

```shell
$ git submodule add https://github.com/locomotivemtl/charcoal-legacy-cms-ext www/modules/cms-ext
```

## Features

_TBD_
