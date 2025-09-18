document.addEventListener('DOMContentLoaded', function() {
    // Menu mobile
    const menuToggle = document.querySelector('.menu-toggle');
    const navMenu = document.querySelector('.nav-menu');

    if (menuToggle) {
        menuToggle.addEventListener('click', function() {
            navMenu.classList.toggle('active');
        });
    }

    // Système d'évaluation par étoiles
    const stars = document.querySelectorAll('.stars i');
    const ratingInput = document.getElementById('rating');
    const ratingText = document.querySelector('.rating-text');

    if (stars.length > 0) {
        stars.forEach(star => {
            star.addEventListener('click', function() {
                const value = this.getAttribute('data-value');
                ratingInput.value = value;
                
                // Mise à jour du texte
                updateRatingText(value);
                
                // Mise à jour des étoiles
                updateStars(value);
            });
        });
    }

    // Fonction pour mettre à jour le texte d'évaluation
    function updateRatingText(value) {
        if (!ratingText) return;
        
        const ratings = {
            '1': 'Très mauvais',
            '2': 'Mauvais',
            '3': 'Moyen',
            '4': 'Bon',
            '5': 'Excellent'
        };
        
        ratingText.textContent = ratings[value] || 'Aucune évaluation';
    }

    // Fonction pour mettre à jour l'affichage des étoiles
    function updateStars(value) {
        if (stars.length === 0) return;
        
        stars.forEach(star => {
            const starValue = star.getAttribute('data-value');
            if (starValue <= value) {
                star.classList.remove('far');
                star.classList.add('fas');
            } else {
                star.classList.remove('fas');
                star.classList.add('far');
            }
        });
    }

    // Gestion du champ "Autre Opérateur"
    const operatorSelect = document.getElementById('operator');
    const otherOperatorGroup = document.getElementById('otherOperatorGroup');

    if (operatorSelect && otherOperatorGroup) {
        operatorSelect.addEventListener('change', function() {
            if (this.value === 'Autre') {
                otherOperatorGroup.style.display = 'block';
            } else {
                otherOperatorGroup.style.display = 'none';
            }
        });
    }

    // Gestion des formulaires
    const complaintForm = document.getElementById('complainForm');
    const contactForm = document.getElementById('contactForm');
    const formSuccess = document.getElementById('formSuccess');

    // Formulaire de plainte
    if (complaintForm) {
        complaintForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Simuler l'envoi du formulaire
            setTimeout(function() {
                complaintForm.style.display = 'none';
                formSuccess.style.display = 'block';
            }, 1000);
        });
    }

    // Formulaire de contact
    if (contactForm) {
        contactForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Simuler l'envoi du formulaire
            setTimeout(function() {
                contactForm.style.display = 'none';
                formSuccess.style.display = 'block';
            }, 1000);
        });
    }

    // Fonction pour réinitialiser le formulaire
    window.resetForm = function() {
        if (complaintForm) {
            complaintForm.reset();
            complaintForm.style.display = 'block';
            formSuccess.style.display = 'none';
            
            // Réinitialiser les étoiles
            if (stars.length > 0) {
                stars.forEach(star => {
                    star.classList.remove('fas');
                    star.classList.add('far');
                });
                
                ratingInput.value = '';
                ratingText.textContent = 'Aucune évaluation';
            }
            
            // Réinitialiser le champ autre opérateur
            if (otherOperatorGroup) {
                otherOperatorGroup.style.display = 'none';
            }
        }
        
        if (contactForm) {
            contactForm.reset();
            contactForm.style.display = 'block';
            formSuccess.style.display = 'none';
        }
    };

    // Chargement des plaintes récentes sur la page d'accueil
    const recentComplaintsList = document.getElementById('recent-complaints-list');
    if (recentComplaintsList) {
        // Simuler le chargement des données
        setTimeout(function() {
            const loadingText = recentComplaintsList.querySelector('.loading-text');
            if (loadingText) {
                loadingText.remove();
            }
            
            // Plaintes fictives
            const recentComplaints = [
                {
                    title: "Internet mobile très lent à Yaoundé",
                    operator: "MTN Cameroun",
                    location: "Centre, Yaoundé",
                    date: "12/05/2025",
                    rating: 2,
                    description: "Depuis une semaine, la connexion internet mobile est extrêmement lente, même avec une bonne couverture réseau. Impossible de charger des pages web ou d'utiliser des applications.",
                    category: "Vitesse Internet"
                },
                {
                    title: "Problèmes de connexion récurrents",
                    operator: "Orange Cameroun",
                    location: "Littoral, Douala",
                    date: "11/05/2025",
                    rating: 1,
                    description: "Je perds constamment le signal dans le quartier Akwa, ce qui rend impossible les appels professionnels importants. Le service client n'a pas de solution.",
                    category: "Couverture réseau"
                }
            ];
            
            // Afficher les plaintes
            recentComplaints.forEach(complaint => {
                const complaintCard = createComplaintCard(complaint);
                recentComplaintsList.appendChild(complaintCard);
            });
            
            // Mise à jour des statistiques
            updateStats();
        }, 1500);
    }

    // Fonction pour créer une carte de plainte
    function createComplaintCard(complaint) {
        const card = document.createElement('div');
        card.className = 'complaint-card';
        
        // Créer les étoiles HTML
        let starsHtml = '';
        for (let i = 1; i <= 5; i++) {
            if (i <= complaint.rating) {
                starsHtml += '<i class="fas fa-star"></i> ';
            } else {
                starsHtml += '<i class="far fa-star"></i> ';
            }
        }
        
        card.innerHTML = `
            <div class="complaint-header">
                <h3>${complaint.title}</h3>
                <div class="complaint-meta">
                    <span class="operator"><i class="fas fa-broadcast-tower"></i> ${complaint.operator}</span>
                    <span class="location"><i class="fas fa-map-marker-alt"></i> ${complaint.location}</span>
                    <span class="date"><i class="far fa-calendar-alt"></i> ${complaint.date}</span>
                    <span class="rating">${starsHtml}</span>
                </div>
            </div>
            <div class="complaint-body">
                <p>${complaint.description}</p>
            </div>
            <div class="complaint-footer">
                <div class="category-tag">${complaint.category}</div>
            </div>
        `;
        
        return card;
    }

    // Fonction pour mettre à jour les statistiques
    function updateStats() {
        const totalPlaintes = document.getElementById('total-plaintes');
        const totalUtilisateurs = document.getElementById('total-utilisateurs');
        const totalRegions = document.getElementById('total-regions');
        
        if (totalPlaintes) totalPlaintes.textContent = "247";
        if (totalUtilisateurs) totalUtilisateurs.textContent = "189";
        if (totalRegions) totalRegions.textContent = "10";
    }

    // Gestion de pagination
    const prevPageBtn = document.getElementById('prev-page');
    const nextPageBtn = document.getElementById('next-page');
    const currentPageElem = document.getElementById('current-page');
    const totalPagesElem = document.getElementById('total-pages');
    
    if (prevPageBtn && nextPageBtn && currentPageElem && totalPagesElem) {
        let currentPage = 1;
        const totalPages = 5;
        
        updatePagination();
        
        prevPageBtn.addEventListener('click', function() {
            if (currentPage > 1) {
                currentPage--;
                updatePagination();
            }
        });
        
        nextPageBtn.addEventListener('click', function() {
            if (currentPage < totalPages) {
                currentPage++;
                updatePagination();
            }
        });
        
        function updatePagination() {
            currentPageElem.textContent = currentPage;
            totalPagesElem.textContent = totalPages;
            
            prevPageBtn.disabled = currentPage === 1;
            nextPageBtn.disabled = currentPage === totalPages;
        }
    }

    // Gestion des filtres
    const applyFiltersBtn = document.getElementById('apply-filters');
    if (applyFiltersBtn) {
        applyFiltersBtn.addEventListener('click', function() {
            // Simuler l'application des filtres
            alert('Les filtres ont été appliqués. Cette fonctionnalité nécessitera une base de données en production.');
        });
    }
});