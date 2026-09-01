<?php

namespace FluxErp\Traits\Livewire;

trait LoadsMediaTree
{
    // A trait, not a mount() on FolderTree: that would force its signature on every
    // subclass, and a customer component with its own mount() then dies at class load.
    public function mountLoadsMediaTree(): void
    {
        $this->mediaTree = $this->getTree();
    }
}
