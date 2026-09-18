<?php

namespace App\Http\Controllers;

use App\Models\EffetActif;
use App\Models\Objet;
use App\Models\Personnage;
use App\Models\PersonnageObjet;
use App\Services\FicheDePersonnageService;
use App\Services\InventaireService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class InventaireController extends Controller
{
    public function __construct(
        private readonly InventaireService $inventaire,
        private readonly FicheDePersonnageService $fiche,
    ) {}

    public function index(Personnage $personnage): View
    {
        $this->authorizeOwner($personnage);

        $this->inventaire->purgerEffetsExpires($personnage);
        $personnage->load(['race', 'classe', 'objets', 'effetsActifs']);

        return view('personnages.inventaire', [
            'personnage' => $personnage,
            'classeArmure' => $this->fiche->classeArmure($personnage),
            'bonusActifs' => $this->inventaire->bonusActifs($personnage),
        ]);
    }

    public function ajouterPage(Personnage $personnage): View
    {
        $this->authorizeOwner($personnage);

        $catalogue = Objet::query()
            ->where(function ($query) {
                $query->whereNull('utilisateur_id')->orWhere('utilisateur_id', Auth::id());
            })
            ->orderBy('type')
            ->orderBy('nom')
            ->get();

        return view('personnages.inventaire-ajouter', [
            'personnage' => $personnage,
            'catalogue' => $catalogue,
        ]);
    }

    public function ajouterCustomPage(Personnage $personnage): View
    {
        $this->authorizeOwner($personnage);

        return view('personnages.inventaire-ajouter-custom', [
            'personnage' => $personnage,
        ]);
    }

    public function ajouter(Request $request, Personnage $personnage): RedirectResponse
    {
        $this->authorizeOwner($personnage);

        $validated = $request->validate([
            'objet_id' => [
                'required',
                'integer',
                Rule::exists('objets', 'id')->where(function ($query) {
                    $query->whereNull('utilisateur_id')->orWhere('utilisateur_id', Auth::id());
                }),
            ],
            'quantite' => 'nullable|integer|min:1|max:999',
        ]);

        $objet = Objet::findOrFail($validated['objet_id']);

        $this->inventaire->ajouter($personnage, $objet, $validated['quantite'] ?? 1);

        return redirect()->route('personnages.inventaire.index', $personnage)
            ->with('success', "{$objet->nom} ajouté à l'inventaire.");
    }

    public function ajouterCustom(Request $request, Personnage $personnage): RedirectResponse
    {
        $this->authorizeOwner($personnage);

        $validated = $request->validate([
            'nom' => 'required|string|max:100',
            'description' => 'nullable|string|max:1000',
            'type' => ['required', Rule::in(Objet::TYPES)],
            'categorie' => 'nullable|string|max:50',
            'quantite' => 'nullable|integer|min:1|max:999',
            'magique' => 'nullable|boolean',
            'rarete' => ['nullable', Rule::in(Objet::RARETES)],
            'bonus_toujours_actif' => 'nullable|boolean',
            'bonus' => 'nullable|array',
            'bonus.*' => 'nullable|integer|min:-20|max:20',
            'degats' => 'nullable|string|max:20',
            'type_degats' => ['nullable', Rule::in(['tranchant', 'perforant', 'contondant'])],
            'caracteristique_degats' => ['nullable', Rule::in(['force', 'dexterite'])],
            'ca_base' => 'nullable|integer|min:1|max:30',
            'effet_pv' => 'nullable|integer|min:-100|max:100',
            'duree_valeur' => 'nullable|integer|min:1|max:999',
            'duree_unite' => ['nullable', Rule::in(['tours', 'minutes'])],
        ]);

        $estPotion = $validated['type'] === 'potion';
        $estArme = $validated['type'] === 'arme';
        $estArmure = $validated['type'] === 'armure';
        $magique = (bool) ($validated['magique'] ?? false);

        $description = $validated['description'] ?? null;
        if ($estPotion && ! empty($validated['duree_valeur'])) {
            $unite = $validated['duree_unite'] ?? 'tours';
            $phraseDuree = "Durée : {$validated['duree_valeur']} {$unite}.";
            $description = $description ? "{$description}\n\n{$phraseDuree}" : $phraseDuree;
        }

        $bonusSaisi = array_filter($validated['bonus'] ?? [], fn ($valeur) => (int) $valeur !== 0);

        // Pour une potion, le bonus saisi n'est pas un bonus d'équipement
        // permanent (une potion se consomme, ne s'équipe pas) : il devient
        // un effet temporaire appliqué à l'utilisation, actif tant que sa
        // durée réelle (convertie en secondes) n'est pas écoulée — voir
        // InventaireService::utiliser()/bonusActifs(). Sans durée indiquée,
        // un bonus saisi par erreur est simplement ignoré plutôt que
        // rendu permanent.
        $effetUtilisation = null;
        if ($estPotion) {
            $effetUtilisation = [];
            if (! empty($validated['effet_pv'])) {
                $effetUtilisation['pv'] = (int) $validated['effet_pv'];
            }
            if ($bonusSaisi && ! empty($validated['duree_valeur'])) {
                $unite = $validated['duree_unite'] ?? 'tours';
                $effetUtilisation['bonus'] = $bonusSaisi;
                $effetUtilisation['duree_valeur'] = (int) $validated['duree_valeur'];
                $effetUtilisation['duree_unite'] = $unite;
                $effetUtilisation['duree_secondes'] = (int) $validated['duree_valeur'] * ($unite === 'minutes' ? 60 : 6);
            }
            $effetUtilisation = $effetUtilisation ?: null;
        }

        $coutMains = match (true) {
            $validated['type'] === 'bouclier' => 1,
            $estArme && $validated['categorie'] === 'une_main' => 1,
            $estArme && in_array($validated['categorie'], ['deux_mains', 'distance'], true) => 2,
            default => 0,
        };

        $objet = Objet::create([
            'utilisateur_id' => Auth::id(),
            'nom' => $validated['nom'],
            'description' => $description,
            'type' => $validated['type'],
            'magique' => $magique,
            'rarete' => $magique ? ($validated['rarete'] ?? null) : null,
            'categorie' => $validated['categorie'] ?? null,
            'cout_mains' => $coutMains,
            'bonus' => $estPotion ? null : ($bonusSaisi ?: null),
            'bonus_toujours_actif' => $estPotion ? false : (bool) ($validated['bonus_toujours_actif'] ?? false),
            'est_consommable' => $estPotion,
            'effet_utilisation' => $effetUtilisation,
            'degats' => $estArme ? ($validated['degats'] ?? null) : null,
            'type_degats' => $estArme ? ($validated['type_degats'] ?? null) : null,
            'caracteristique_degats' => $estArme ? ($validated['caracteristique_degats'] ?? null) : null,
            'ca_base' => $estArmure ? ($validated['ca_base'] ?? null) : null,
        ]);

        $this->inventaire->ajouter($personnage, $objet, $validated['quantite'] ?? 1);

        return redirect()->route('personnages.inventaire.index', $personnage)
            ->with('success', "{$objet->nom} créé et ajouté à l'inventaire.");
    }

    public function ajusterQuantite(Request $request, Personnage $personnage, PersonnageObjet $personnageObjet): RedirectResponse
    {
        $this->authorizeLigne($personnage, $personnageObjet);

        $validated = $request->validate([
            'delta' => 'required|integer',
        ]);

        $this->inventaire->ajusterQuantite($personnageObjet, $validated['delta']);

        return back();
    }

    public function equiper(Personnage $personnage, PersonnageObjet $personnageObjet): RedirectResponse
    {
        $this->authorizeLigne($personnage, $personnageObjet);

        try {
            $this->inventaire->equiper($personnage, $personnageObjet);
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors());
        }

        return back()->with('success', 'Objet équipé.');
    }

    public function desequiper(Personnage $personnage, PersonnageObjet $personnageObjet): RedirectResponse
    {
        $this->authorizeLigne($personnage, $personnageObjet);

        $this->inventaire->desequiper($personnageObjet);

        return back()->with('success', 'Objet déséquipé.');
    }

    public function utiliser(Personnage $personnage, PersonnageObjet $personnageObjet): RedirectResponse
    {
        $this->authorizeLigne($personnage, $personnageObjet);

        try {
            $this->inventaire->utiliser($personnage, $personnageObjet);
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors());
        }

        return back()->with('success', 'Objet utilisé.');
    }

    public function retirer(Personnage $personnage, PersonnageObjet $personnageObjet): RedirectResponse
    {
        $this->authorizeLigne($personnage, $personnageObjet);

        $this->inventaire->retirer($personnageObjet);

        return back()->with('success', 'Objet retiré de l\'inventaire.');
    }

    /**
     * Décompte manuellement un effet temporaire en tours (voir
     * InventaireService::ajusterTours()) : les effets en minutes, eux,
     * expirent tout seuls et n'ont pas besoin de cette action.
     */
    public function ajusterTours(Request $request, Personnage $personnage, EffetActif $effetActif): RedirectResponse
    {
        $this->authorizeEffet($personnage, $effetActif);

        $validated = $request->validate([
            'delta' => 'required|integer',
        ]);

        $this->inventaire->ajusterTours($effetActif, $validated['delta']);

        return back();
    }

    private function authorizeOwner(Personnage $personnage): void
    {
        abort_unless($personnage->utilisateur_id === Auth::id(), 403);
    }

    private function authorizeLigne(Personnage $personnage, PersonnageObjet $personnageObjet): void
    {
        $this->authorizeOwner($personnage);
        abort_unless($personnageObjet->personnage_id === $personnage->id, 404);
    }

    private function authorizeEffet(Personnage $personnage, EffetActif $effetActif): void
    {
        $this->authorizeOwner($personnage);
        abort_unless($effetActif->personnage_id === $personnage->id, 404);
    }
}
