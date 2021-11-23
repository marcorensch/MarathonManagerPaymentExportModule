<?php
defined('_JEXEC') or die;
?>

<?php if($events && count($events)):?>
    <div class="nx-registrations">
        <!-- This is the nav containing the toggling elements -->
        <ul uk-switcher="animation: uk-animation-fade" hidden>
            <li><a href="#">Main</a></li>
            <?php foreach ($events as $event):?>
                <li><a href="#"><?php echo $event->name;?></a></li>
            <?php endforeach;?>
        </ul>

        <!-- This is the container of the content items -->
        <ul id="nx-export-switcher" class="uk-switcher">
            <li>
                <div class="uk-child-width-1-2 uk-child-width-1-4@m uk-grid-small" uk-grid uk-scrollspy="target:>div>.uk-card; cls:uk-animation-fade; delay:70; repeat:true;" >
                    <?php
                    foreach ($events as $event){
                        include 'widgets/event.php';
                    }
                    ?>
                </div>
            </li>
            <li>
                <div class="uk-position-relative">
                    <button class="uk-button uk-button-primary uk-button-small" href="#" uk-switcher-item="0">Zurück</button>
                    <div class="uk-margin-top uk-padding-small uk-position-relative event-content" style="min-height: 500px;">
                        <div class="uk-child-width-expand uk-flex uk-flex-middle" uk-grid>
                            <div><h2 id="eventname">NAME</h2></div>
                            <div class="uk-width-auto"><button class="uk-button uk-button-secondary exportBtn">Teilnehmer Exportieren</button></div>
                            <!--<div class="uk-width-auto"><button class="uk-button uk-button-primary countriesBtn">Check Countries</button></div>-->
                        </div>
                        <div id="teams">
                            <?php include 'widgets/search.php';?>
                            <table class="uk-table uk-table-striped uk-table-hover" id="registrationsTable">
                                <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Team</th>
                                    <th>Kontakt</th>
                                    <th>Telefon</th>
                                    <th>Referenznummer</th>
                                    <th>Startgebühr</th>
                                </tr>
                                </thead>
                                <tbody>
                                <tr>
                                    <!-- Liste der Teams mit AJAX befüllt -->
                                </tr>
                                </tbody>
                            </table>
                        </div>
                        <div class="waiter uk-position-cover uk-flex uk-flex-top uk-flex-center" style="background:#fff;">
                            <div class="uk-padding-large">
                                <div class="uk-margin uk-text-center"><div uk-spinner="ratio: 4"></div></div>
                                <div><span class="uk-heading-small uk-text-muted">Lade Inhalte</span></div>
                            </div>
                        </div>
                    </div>
                </div>
            </li>
        </ul>
        <div class="exportlink uk-hidden">
            <a id="export-download" href="#">Download File</a>
        </div>
    </div>
<?php endif;
