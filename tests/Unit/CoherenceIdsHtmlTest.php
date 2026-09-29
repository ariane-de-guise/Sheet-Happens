<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

/**
 * Garde-fou contre les renommages incomplets : tout identifiant HTML
 * utilisé par le JavaScript (vues Blade et public/js) doit être déclaré
 * par un attribut id="..." quelque part. Un id orphelin casse le script
 * sans qu'aucun test PHP ne le remarque.
 */
class CoherenceIdsHtmlTest extends TestCase
{
    private function contenuDesFichiers(): string
    {
        $racine = dirname(__DIR__, 2);
        $contenu = '';

        foreach (['resources/views', 'public/js'] as $dossier) {
            $fichiers = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator("$racine/$dossier"));
            foreach ($fichiers as $fichier) {
                if ($fichier->isFile() && preg_match('/\.(php|js)$/', $fichier->getFilename())) {
                    $contenu .= file_get_contents($fichier->getPathname())."\n";
                }
            }
        }

        return $contenu;
    }

    public function test_chaque_id_utilise_par_le_javascript_est_declare_dans_le_html(): void
    {
        $contenu = $this->contenuDesFichiers();

        preg_match_all('/\bid\s*=\s*["\']([\w-]+)/', $contenu, $declares);
        $declares = array_unique($declares[1]);

        // Ids lus directement, ou passés en argument à une fonction qui les
        // lira (ex. filtrerParHistorique('ideal_liste', ...)).
        preg_match_all('/getElementById\(\s*[\'"]([\w-]+)[\'"]\s*\)/', $contenu, $directs);
        preg_match_all('/querySelector(?:All)?\(\s*[\'"][^\'"]*?#([\w-]+)/', $contenu, $selecteurs);
        preg_match_all('/\(\s*\'([a-z]+(?:_[a-z]+)*_liste|[a-z]+_select|edition_[a-z_]+)\'/', $contenu, $arguments);

        $utilises = array_unique(array_merge($directs[1], $selecteurs[1], $arguments[1]));
        $orphelins = array_values(array_diff($utilises, $declares));

        $this->assertSame([], $orphelins, 'Ids utilisés par le JavaScript mais absents du HTML : '.implode(', ', $orphelins));
    }
}
