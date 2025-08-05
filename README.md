# Mastodon Feed plugin for Kirby

![Plugin Preview](src/assets/mastodon-feed-preview.jpg)

Display posts from Mastodon on your Kirby-powered site. Includes a variety of configuration options and caching for improved performance.


## Installation

### [Kirby CLI](https://github.com/getkirby/cli)

```bash
kirby plugin:install scottboms/mastodon-feed
```

### Git Submodule

```bash
$ git submodule add https://github.com/scottboms/kirby-mastodon-feed.git site/plugins/mostadon-feed
```

### Copy and Paste

1. [Download](https://github.com/scottboms/kirby-mastodon-feed/archive/master.zip) the contents of this repository as Zip file.
2. Rename the extracted folder to `mastodon-feed` and copy it into the `site/plugins/` directory in your Kirby project.


## Usage

Add the following line to include the built-in snippet to your site. You can customize the HTML formatting by creating a snippet with the same name in your `/site/snippets/` folder.

```php
<?= snippet('mastodon_feed') ?>
```


## Configuration Options

| Property        | Default         | Required? | Description                                    |
--------------------------------------------------------------------------------------------------
| username        | `null`          | Yes       | Your Mastodon username                         |
| instance        | `null`          | Yes       | Your Mastodon instance (e.g. mastodon.social)  |
| cache           | `true`          | No        | Caches data returned from Mastodon API.        |
| cachettl        | `900`           | No        | Cache timeout - 15 minutes default (900s)      |
| wrapper         | `div`           | No        | Wrapper element to output around snippet       |
| class           | `mastodon-feed` | No        | Class name applied to wrapper                  |
| limit           | `20`            | No        | Number of results to return/display            |
| dateformat      | `M d, Y`        | No        | Adjust date format per PHP datetime formats    |
| excludereplies  | `true`          | No        | Exclude replies from results?                  |
| onlymedia       | `false`         | No        | Only show posts with media attachments?        |

Date formatting follows the [available format options from PHP](https://php.net/manual/en/function.date.php).

Example Config:

```php
<?php
  return [
	  'scottboms.mastodon' => [
      'username'   => 'scottboms',
      'instance'   => 'mastodon.social',
      'wrapper'    => 'section',
      'class'      => 'mastodon',
      'dateformat' => 'm-d-Y',
      'cache'      => true,
      'cachettl'   => 300 // 5 minutes
      'onlymedia'  => true,
    ]
  ]
```


## Compatibility

* Kirby 4.x
* Kirby 5.x


## Disclaimer

This plugin is provided "as is" with no guarantee. Use it at your own risk and always test before using it in a production environment. If you identify an issue, typo, etc, please [create a new issue](https://github.com/scottboms/kirby-mastodon-feed/issues/new) so I can investigate.


## License

[MIT](https://opensource.org/licenses/MIT)
