// Vérification si l'utilisateur a déjà accepté ou refusé les cookies
if (!localStorage.getItem('cookies_consent')) {
    // Si aucun consentement (ni accepté ni refusé), affiche la bannière
    document.getElementById('cookie-banner').style.display = 'block';
} else {
    // Si un consentement a déjà été donné, cache la bannière
    document.getElementById('cookie-banner').style.display = 'none';
}

// Gère l'acceptation des cookies
document.getElementById('accept-cookies').addEventListener('click', function () {
    // Cache la bannière
    document.getElementById('cookie-banner').style.display = 'none';

    // Sauvegarde dans localStorage que l'utilisateur a accepté les cookies
    localStorage.setItem('cookies_consent', 'accepted');
    console.log('Cookies acceptés');
});

// Gère le refus des cookies
document.getElementById('decline-cookies').addEventListener('click', function () {
    // Cache la bannière
    document.getElementById('cookie-banner').style.display = 'none';

    // Sauvegarde dans localStorage que l'utilisateur a refusé les cookies
    localStorage.setItem('cookies_consent', 'declined');
    console.log('Cookies refusés');
});

// Ouvre le modal lorsque l'utilisateur clique sur "Politique du site"
document.getElementById('privacy-button')?.addEventListener('click', function () {
    console.log('Politique du site cliquée');
    document.getElementById('privacy-modal').style.display = 'block'; // Affiche la modale
});

// Ferme le modal lorsque l'utilisateur clique sur "Fermer"
document.getElementById('close-modal')?.addEventListener('click', function () {
    console.log('Modal fermé');
    document.getElementById('privacy-modal').style.display = 'none'; // Cache la modale
});
