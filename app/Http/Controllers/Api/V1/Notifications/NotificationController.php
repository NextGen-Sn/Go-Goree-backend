<?php

namespace App\Http\Controllers\Api\V1\Notifications;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * Contrôleur pour gérer les notifications in-app des utilisateurs.
 */
class NotificationController extends Controller
{
    /**
     * Liste des notifications de l'utilisateur connecté.
     */
    public function index(Request $request)
    {
        return response()->json(Notification::where('user_id', $request->user()->id)->paginate());
    }

    /**
     * Afficher les détails d'une notification spécifique (propriétaire uniquement).
     */
    public function show(Request $request, $id)
    {
        $notif = $this->notificationDeLUtilisateur($request, $id);

        return response()->json($notif);
    }

    /**
     * Marquer une notification comme lue (propriétaire uniquement).
     */
    public function update(Request $request, $id)
    {
        $notif = $this->notificationDeLUtilisateur($request, $id);
        $notif->update(['lu_a' => now()]);

        return response()->json($notif);
    }

    /**
     * Supprimer une notification (propriétaire uniquement).
     */
    public function destroy(Request $request, $id)
    {
        $notif = $this->notificationDeLUtilisateur($request, $id);
        $notif->delete();

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * Récupère la notification en s'assurant qu'elle appartient à l'utilisateur
     * connecté (404 si elle n'existe pas ou n'est pas la sienne).
     */
    private function notificationDeLUtilisateur(Request $request, string $id): Notification
    {
        return Notification::where('user_id', $request->user()->id)->findOrFail($id);
    }
}
