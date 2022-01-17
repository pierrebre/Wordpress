window.addEventListener("load", function () {

  //recuperation des tabs
  var tabs = document.querySelectorAll("ul.nav-tabs > li");


  //ecouteur d'évennement sur le click de la tab souhaité pour déclencher le changement de tab
  for (i = 0; i < tabs.length; i++) {
    tabs[i].addEventListener("click", switchTab);
  }

  // Methode pour changer la tab
  function switchTab(event) {

    // Empécher la propagation du code
    event.preventDefault();

    //supression de la class active sur la tab en cours ( hidden)
    document.querySelector("ul.nav-tabs li.active").classList.remove("active");
    document.querySelector(".tab-pane.active").classList.remove("active");

    // récuperation de la cible cliqué
    var clickedTab = event.currentTarget;
    var anchor = event.target;
    var activePaneID = anchor.getAttribute("href");
    
    // Ajout de la class active sur la tab cliqué
    clickedTab.classList.add("active");
    document.querySelector(activePaneID).classList.add("active");
  }
});
