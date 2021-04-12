let handleDisplayToTricksBtn = function () {
    window.addEventListener("scroll", function () {
        let currentPos = (window.pageYOffset || document.documentElement.scrollTop);
        let btn = $("#tricksBtn");
        if (currentPos < $("#tricks-container").position().top) {
            btn.show();
        } else {
            btn.hide();
        }
    });
};

let handleDisplayGoToTopBtn = function () {
    let btn = $("#topBtn");
    window.addEventListener("scroll", function () {
        let currentPos = (window.pageYOffset || document.documentElement.scrollTop);
        let tricksContainer = $("#tricks-container");
        if (tricksContainer.children().length >= 15 && currentPos > tricksContainer.position().top) {
            btn.show();
        } else {
            btn.hide();
        }
    });
};

window.onload = function () {
    handleDisplayToTricksBtn();
    handleDisplayGoToTopBtn();
};
