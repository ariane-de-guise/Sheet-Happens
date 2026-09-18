<?php

namespace Database\Seeders;

use App\Models\ClasseSort;
use App\Models\Sort;
use Illuminate\Database\Seeder;

class ClasseSortSeeder extends Seeder
{
    public function run(): void
    {
        /*
         * Classes fournies :
         * 1 = Barbare
         * 2 = Clerc
         * 3 = Roublard
         * 4 = Magicien
         * 5 = Guerrier
         *
         * En 5e 2014, parmi ces classes, seules les classes Clerc
         * et Magicien ont une liste de sorts de classe.
         *
         * Les sous-classes qui accordent des sorts supplémentaires
         * sont traitées dans SousclasseSortSeeder.
         *
         * Niveau 0 à 3 uniquement, conformément au SortSeeder actuel.
         */

        $sortsParClasse = [
            2 => [
            'Guidance',
            'Light',
            'Mending',
            'Resistance',
            'Sacred Flame',
            'Spare the Dying',
            'Thaumaturgy',
            'Bane',
            'Bless',
            'Command',
            'Create or Destroy Water',
            'Cure Wounds',
            'Detect Evil and Good',
            'Detect Magic',
            'Detect Poison and Disease',
            'Guiding Bolt',
            'Healing Word',
            'Inflict Wounds',
            'Protection from Evil and Good',
            'Purify Food and Drink',
            'Sanctuary',
            'Shield of Faith',
            'Aid',
            'Augury',
            'Blindness/Deafness',
            'Calm Emotions',
            'Continual Flame',
            'Enhance Ability',
            'Find Traps',
            'Gentle Repose',
            'Hold Person',
            'Lesser Restoration',
            'Locate Object',
            'Prayer of Healing',
            'Protection from Poison',
            'Silence',
            'Spiritual Weapon',
            'Warding Bond',
            'Zone of Truth',
            'Animate Dead',
            'Beacon of Hope',
            'Bestow Curse',
            'Clairvoyance',
            'Create Food and Water',
            'Daylight',
            'Dispel Magic',
            'Glyph of Warding',
            'Magic Circle',
            'Mass Healing Word',
            'Meld into Stone',
            'Protection from Energy',
            'Remove Curse',
            'Revivify',
            'Sending',
            'Speak with Dead',
            'Spirit Guardians',
            'Tongues',
            'Water Walk',
            ], // Clerc

            4 => [
            'Acid Splash',
            'Chill Touch',
            'Dancing Lights',
            'Fire Bolt',
            'Light',
            'Mage Hand',
            'Mending',
            'Message',
            'Minor Illusion',
            'Poison Spray',
            'Prestidigitation',
            'Ray of Frost',
            'Shocking Grasp',
            'True Strike',
            'Alarm',
            'Burning Hands',
            'Charm Person',
            'Color Spray',
            'Comprehend Languages',
            'Detect Magic',
            'Disguise Self',
            'Expeditious Retreat',
            'False Life',
            'Feather Fall',
            'Find Familiar',
            'Fog Cloud',
            'Grease',
            'Identify',
            'Illusory Script',
            'Jump',
            'Longstrider',
            'Mage Armor',
            'Magic Missile',
            'Protection from Evil and Good',
            'Shield',
            'Silent Image',
            'Sleep',
            'Thunderwave',
            'Unseen Servant',
            'Alter Self',
            'Arcane Lock',
            'Blindness/Deafness',
            'Blur',
            'Continual Flame',
            'Darkness',
            'Darkvision',
            'Detect Thoughts',
            'Enlarge/Reduce',
            'Flaming Sphere',
            'Gentle Repose',
            'Gust of Wind',
            'Hold Person',
            'Invisibility',
            'Knock',
            'Levitate',
            'Locate Object',
            'Magic Mouth',
            'Magic Weapon',
            'Mirror Image',
            'Misty Step',
            'Ray of Enfeeblement',
            'Rope Trick',
            'Scorching Ray',
            'See Invisibility',
            'Shatter',
            'Spider Climb',
            'Suggestion',
            'Web',
            'Animate Dead',
            'Bestow Curse',
            'Blink',
            'Clairvoyance',
            'Counterspell',
            'Dispel Magic',
            'Fear',
            'Fireball',
            'Fly',
            'Gaseous Form',
            'Glyph of Warding',
            'Haste',
            'Hypnotic Pattern',
            'Lightning Bolt',
            'Magic Circle',
            'Major Image',
            'Nondetection',
            'Phantom Steed',
            'Protection from Energy',
            'Remove Curse',
            'Sending',
            'Sleet Storm',
            'Slow',
            'Stinking Cloud',
            'Tongues',
            'Vampiric Touch',
            'Water Breathing',
            ], // Magicien
        ];

        $sorts = Sort::query()
            ->whereIn('NomAnglais', array_values(array_unique(
                array_merge(...array_values($sortsParClasse))
            )))
            ->pluck('id', 'NomAnglais');

        foreach ($sortsParClasse as $idClasse => $nomsAnglais) {
            foreach ($nomsAnglais as $nomAnglais) {
                if (!isset($sorts[$nomAnglais])) {
                    throw new \RuntimeException(
                        "Le sort [$nomAnglais] est absent de la table sorts."
                    );
                }

                ClasseSort::updateOrCreate(
                    [
                        'id_Classe' => $idClasse,
                        'id_Sort' => $sorts[$nomAnglais],
                    ],
                    []
                );
            }
        }
    }
}
