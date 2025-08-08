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

## Template Output

If you want to explore the data returned via the plugin to the snippet or that you can render directly in a template, you can use the following to output the contents of the `$feed` object returned.

```php
<pre>
	<?php print_r($items->toArray()); ?>
</pre>
```


## Configuration Options

| Property                      | Default  | Req? | Description                                    |
|-------------------------------|----------|------|------------------------------------------------|
| cache.scottboms.mastodon.type | `file`   | Yes  | Required to enable cache type for the plugin   |
| username                      | `null`   | Yes  | Your Mastodon username                         |
| server                        | `null`   | Yes  | Your Mastodon server (e.g. mastodon.social)    |
| cache                         | `true`   | No   | Caches data returned from Mastodon API.        |
| cachettl                      | `900`    | No   | Cache timeout - 15 minutes default (900s)      |
| limit                         | `20`     | No   | Number of results to return/display (max 40)   |
| dateformat                    | `M d, Y` | No   | Adjust date format per PHP datetime formats    |
| excludereplies                | `true`   | No   | Exclude replies from results?                  |
| onlymedia                     | `false`  | No   | Only show posts with media attachments?        |
| panel.limit                   | `12`     | No   | Number of results to display in the Panel Area |


Date formatting follows the [available format options from PHP](https://php.net/manual/en/function.date.php).

Example Config:

```php
<?php
  return [
    'cache' => [
      'scottboms.mastodon' => [
        'type' => 'file',
      ]
    ],

	  'scottboms.mastodon' => [
      'username'   => 'scottboms',
      'instance'   => 'mastodon.social',
      'dateformat' => 'm-d-Y',
      'cache'      => true,
      'cachettl'   => 300 // 5 minutes
      'onlymedia'  => true,
      'panel'      => [
        'limit' => 6
      ]
    ]
  ]
```


## Panel View Button

The plugin includes a custom [Panel View Button](https://getkirby.com/releases/5/view-buttons) for Kirby 5.x to manually clear the Mastodon cache file which can be added to the site or a page blueprint using the `buttons` [option](https://getkirby.com/docs/reference/panel/blueprints/page#view-buttons).


```yml
buttons:
  mastodoncache: true
```


## Compatibility

* Kirby 4.x
* Kirby 5.x


## Disclaimer

This plugin is provided "as is" with no guarantee. Use it at your own risk and always test before using it in a production environment. If you identify an issue, typo, etc, please [create a new issue](https://github.com/scottboms/kirby-mastodon-feed/issues/new) so I can investigate.


## License

[MIT](https://opensource.org/licenses/MIT)
