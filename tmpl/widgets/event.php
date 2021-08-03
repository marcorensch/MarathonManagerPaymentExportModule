<?php
defined('_JEXEC') or die;
$regs = ModMManagerExportHelper::countRegistrations($event->id, false);
$paidregs = ModMManagerExportHelper::countRegistrations($event->id, true);
?>

<div>
    <div class="uk-card uk-card-default uk-card-small uk-card-hover uk-position-relative">
        <div class="uk-height-small uk-background-cover uk-light" data-src="<?php echo $event->headerimg;?>" uk-img>
        </div>
        <div class="uk-card-body">
            <h3 class="uk-card-title"><?php echo $event->name;?></h3>
        </div>
        <div class="uk-card-footer uk-padding-small">
            <div uk-tooltip="<?php echo $regs;?> Team(s) registriert, <?php echo $paidregs;?>  bezahlt" class="uk-text-small uk-text-meta uk-margin-remove">
                <span>Registriert: <?php echo $regs;?>/<span class="uk-text-success"><?php echo $paidregs;?></span></span>
            </div>
            <div class="uk-text-small uk-text-meta uk-margin-remove">Eventdatum: <?php echo $event->eventdate;?></div>
        </div>

        <a data-event-id="<?php echo $event->id;?>" data-event-name="<?php echo $event->name;?>" uk-scroll="#nx-export-switcher" class="uk-position-cover event" href="#" uk-switcher-item="1"></a>

    </div>
</div>
