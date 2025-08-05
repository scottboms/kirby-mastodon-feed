<?php foreach(Scottboms\Mastodon\Feed::formattedFeed() as $item): ?>

	<?php if (!empty($item['isNotice'])): ?>
		<!-- output an message if feed returns empty -->
		<div class="mastodon__notice"><?= esc($item['content']) ?></div>

	<?php else: ?>
		<!-- output data returned from mastodon api -->
		<article class="<?= $item['isBoost'] ? 'boost' : 'original' ?>">
	    <header>
	      <img style="width: auto" width="32" height="32" src="<?= esc($item['avatar']) ?>" alt="">
	      <strong><?= esc($item['author']) ?></strong>
	      <small>
					<?= esc($item['date']) ?> • <a href="<?= esc($item['url']) ?>">Source</a> • 

					<?php if ($item['applicationName']): ?>
						Posted via
				    <?php if ($item['applicationWebsite']): ?>
				      <a href="<?= esc($item['applicationWebsite']) ?>" rel="nofollow noopener"><?= esc($item['applicationName']) ?></a>
				    <?php else: ?>
				      <?= esc($item['applicationName']) ?>
				    <?php endif ?>
					<?php endif ?>
				</small>
	    </header>

	    <?php if (!empty($item['originalContent'])): ?>
	      <div class="original">
	        <?= $item['originalContent'] ?>
	      </div>
	    <?php endif ?>

			<?php foreach ($item['media'] as $media): ?>
				<?php if ($media['type'] === 'image'): ?>
					<img src="<?= esc($media['previewUrl'] ?? $media['url']) ?>" alt="<?= esc($media['description']) ?>">
				<?php elseif ($media['type'] === 'video' || $media['type'] === 'gifv'): ?>
					<video controls>
						<source src="<?= esc($media['url']) ?>" type="video/mp4">
					</video>
				<?php endif ?>
			<?php endforeach ?>

	    <?php if (!empty($item['rebloggedContent'])): ?>
      <div class="reblog">
        <?= $item['rebloggedContent'] ?>

				<?php if ($item['isBoost']): ?>
				<footer class="mastodon__boost">
					<p>Boosted from <a href="<?= esc($item['reblogAuthor']['url']) ?>" rel="nofollow noopener">
    @<?= esc($item['reblogAuthor']['name']) ?></p>
				</footer>
				<?php endif ?>
	      </div>
	    <?php endif ?>
	  </article>

	<?php endif ?>
<?php endforeach ?>
