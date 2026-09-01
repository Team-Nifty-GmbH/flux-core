<?php

namespace FluxErp\Contracts;

/**
 * Marks a FluxAction as safe to run for many records in a single bulk dispatch.
 *
 * Actions implementing this contract must also extend
 * {@see \FluxErp\Actions\DispatchableFluxAction} so each record can be executed
 * as its own queued, monitored job within a batch.
 */
interface SupportsBulkExecution {}
