
<div class="<?php echo $this->class; ?> block"<?php echo $this->cssID; ?><?php if ($this->style): ?> style="<?php echo $this->style; ?>"<?php endif; ?>>
<?php if ($this->headline): ?>

<<?php echo $this->hl; ?>><?php echo $this->headline; ?></<?php echo $this->hl; ?>>
<?php endif; ?>

<a <?php echo strlen($this->showTip) ? 'class="pdftip" ' : ''; ?>href="<?php echo $this->href; ?>" title="<?php echo $this->title; ?>" rel="<?php echo $this->rel; ?>"<?php echo LINK_NEW_WINDOW; ?>><img src="<?php echo $this->preview; ?>"<?php echo $this->previewImgSize; ?><?php if ($this->margin): ?> style="<?php echo $this->margin; ?>"<?php endif; ?> alt="<?php echo $this->title; ?>" class="preview_image" /></a>

<img src="<?php echo $this->icon; ?>"<?php echo $this->imgSize; ?> alt="<?php echo $this->title; ?>" class="mime_icon" /> <a <?php echo strlen($this->showTip) ? 'class="pdftip" ' : ''; ?>href="<?php echo $this->href; ?>" title="<?php echo $this->title; ?>" rel="<?php echo $this->rel; ?>"<?php echo LINK_NEW_WINDOW; ?>><?php echo $this->link; ?></a>

</div>