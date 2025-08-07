<?php
	use Scottboms\Mastodon\Feed;
	$items = Feed::formattedFeed();
?>

<!--<pre>
	<?php print_r($items->toArray()); ?>
</pre>-->

<div class="mastodon-feed">
<?php if ($items->isEmpty()): ?>
	<div class="mastodon__notice">
  	<p>No posts found.</p>
	</div>
<?php else: ?>
	<!-- output data returned from mastodon api -->
	<?php foreach ($items as $item): ?>
	<article class="toot <?= $item->isBoost() ? 'boosted-post' : 'author' ?>">
		<?php if ($item->isBoost() && $item->reblogAuthor()): ?>
			<div class="toot__attribution"><a href="<?= $item->reblogAuthor()->url() ?>" rel="nofollow noopener"><?= esc($item->attribution()) ?></a></div>
		<?php endif ?>
		<header>
			<?php if ($item->avatar()): ?>
			<figure class="toot__avatar">
				<img width="32" height="32" src="<?= esc($item->avatar()) ?>" alt="<?= $item->author() ?>">
			</figure>
			<?php endif ?>

			<div class="toot__meta">
				<?php if (!empty($item->author())): ?>
					<span class="toot__author"><?= $item->author() ?>
						<span class="toot__username">@<?= $item->username() ?></span>
					</span>
				<?php else: ?>
					<span class="toot__author">@<?= $item->username() ?></span>
				<?php endif ?>
				<?php if ($item->date()): ?>
					<time class="toot__date"><?= $item->date() ?></time>
				<?php endif ?>
			</div>
		</header>

		<?php if ($item->originalContent()): ?>
		<div class="toot__content">
			<?= $item->originalContent() /* contains mastodon html */ ?>
		</div>
		<?php endif ?>

		<?php if (!empty($item->rebloggedContent())): ?>
		<div class="toot__reblog__content">
			<?= $item->rebloggedContent() ?>
		</div>
		<?php endif ?>

		<?php if ($item->media()): ?>
		<ul class="toot__media">
			<?php foreach ($item->media() as $m): ?>
				<?php if($m->type() === "image"): ?>
				<li>
					<a href="<?= esc($m->url()) ?>">
						<img src="<?= esc($m->previewUrl() ?? $m->url()) ?>" alt="<?= esc($m->description()) ?>">
					</a>
				</li>
				<?php elseif($m->type() === 'video' || $m->type() === 'gifv'): ?>
				<li>
					<video controls>
						<source src="<?= esc($m->url()) ?>" type="video/mp4">
					</video>
				</li>
				<?php endif ?>
			<?php endforeach ?>
		</ul>
		<?php endif; ?>

		<footer>
			<?php if (!empty($item->applicationName())): ?>
				<p class="toot__app">via <?php if ($item->applicationWebsite()): ?><a href="<?= esc($item->applicationWebsite()) ?>" rel="nofollow noopener"><?= esc($item->applicationName()) ?></a><?php else: ?><?= esc($item->applicationName()) ?><?php endif ?></p>
			<?php endif ?>
		</footer>

	</article>
	<?php endforeach ?>
<?php endif ?>
</div>

