/* =====================================================
   GLOBAL EBOOK SCRIPT – CLEAN & SAFE
   ✔ Single turn.js init
   ✔ No resize destroy
   ✔ Zoom / Fullscreen / Swipe
   ✔ No HierarchyRequestError
===================================================== */

(() => {

    let flipInitialized = false;
    let flipSound;
    let audioUnlocked = false;
/* =====================================
   DOM READY
===================================== */
document.addEventListener("DOMContentLoaded", () => {
    initFileUpload();
    setupAudioUnlock();
    // waitForImagesThenInit();
});

/* =====================================
   FILE UPLOAD
===================================== */
function initFileUpload() {

    const pdfInput        = document.getElementById("pdfInput");
    const folderInput     = document.getElementById("folderInput");
    const selectFilesBtn  = document.getElementById("selectFiles");
    const selectFolderBtn = document.getElementById("selectFolder");

    const fileList  = document.getElementById("fileList");
    const fileItems = document.getElementById("fileItems");
    const fileCount = document.getElementById("fileCount");

    if (!pdfInput && !folderInput) return;

    function updateFileList(files) {
        fileItems.innerHTML = "";
        let count = 0;
        let folderName = null;

        [...files].forEach(file => {
            if (file.type === "application/pdf") {
                count++;
                if (!folderName && file.webkitRelativePath) {
                    folderName = file.webkitRelativePath.split("/")[0];
                }
                const li = document.createElement("li");
                li.textContent = file.name;
                fileItems.appendChild(li);
            }
        });

        if (count > 0) {
            fileList.style.display = "block";
            fileCount.textContent = count;
            if (folderName) {
                const title = document.createElement("li");
                title.style.fontWeight = "bold";
                title.textContent = `📁 Folder: ${folderName}`;
                fileItems.prepend(title);
            }
        }
    }

    selectFilesBtn?.addEventListener("click", () => pdfInput.click());
    selectFolderBtn?.addEventListener("click", () => folderInput.click());
    pdfInput?.addEventListener("change", () => updateFileList(pdfInput.files));
    folderInput?.addEventListener("change", () => updateFileList(folderInput.files));
}
    /* =================================================
       AUDIO UNLOCK
    ================================================= */
    function setupAudio() {
        flipSound = document.getElementById("flipSound");
        if (!flipSound) return;

        const unlock = () => {
            if (audioUnlocked) return;
            flipSound.volume = 0.4;
            flipSound.play().then(() => {
                flipSound.pause();
                flipSound.currentTime = 0;
                audioUnlocked = true;
            }).catch(() => {});
        };

        document.addEventListener("click", unlock, { once: true });
        document.addEventListener("touchstart", unlock, { once: true });
    }

    function playFlipSound() {
        if (!flipSound || !audioUnlocked) return;
        flipSound.currentTime = 0;
        flipSound.play().catch(() => {});
    }

    /* =================================================
       WAIT FOR IMAGES
    ================================================= */
    function waitForImages(container, callback) {
        const imgs = container.querySelectorAll("img");
        if (!imgs.length) return callback();

        let loaded = 0;
        imgs.forEach(img => {
            if (img.complete) loaded++;
            else img.onload = img.onerror = () => {
                loaded++;
                if (loaded === imgs.length) callback();
            };
        });

        if (loaded === imgs.length) callback();
    }

    /* =================================================
       INIT FLIPBOOK (ONLY ONCE)
    ================================================= */
   function initFlipbook() {

    if (flipInitialized) return;

    const $fb = $("#flipbook");
    if (!$fb.length || $fb.data("turn")) return;

    /* ---- ODD PAGE FIX ---- */
    let pageCount = $fb.children(".page").length;
    if (pageCount % 2 !== 0) {
        $fb.append('<div class="page blank"></div>');
        pageCount++;
    }

    const vw = window.innerWidth;
    const vh = window.innerHeight;
    const PAGE_RATIO = 700 / 440;

    let pageH = Math.floor(vh * 0.85);
    let pageW = Math.floor(pageH / PAGE_RATIO);

    if (pageW * 2 > vw * 0.9) {
        pageW = Math.floor(vw * 0.9 / 2);
        pageH = Math.floor(pageW * PAGE_RATIO);
    }

    const doubleWidth = pageW * 2;
    const singleWidth = pageW;

    $fb.turn({
        width: doubleWidth,
        height: pageH,
        autoCenter: true,
        display: "double",
        gradients: true,
        acceleration: true,
        elevation: 50,

        when: {
            turning: function (e, page) {
                const total = $(this).turn("pages");

                // FIRST PAGE → SINGLE (NO WHITE)
                if (page === 1) {
                    $(this).turn("display", "single");
                    $(this).turn("size", singleWidth, pageH);
                    $(this).removeClass("left-single").addClass("right-single");
                }

                // LAST PAGE → SINGLE (NO WHITE)
                else if (page === total) {
                    $(this).turn("display", "single");
                    $(this).turn("size", singleWidth, pageH);
                    $(this).removeClass("right-single").addClass("left-single");
                }

                // MIDDLE → DOUBLE
                else {
                    $(this).turn("display", "double");
                    $(this).turn("size", doubleWidth, pageH);
                    $(this).removeClass("left-single right-single");
                }
            },

            turned: function () {
                updateNavButtons();
            }
        }
    });

    flipInitialized = true;

    attachNavButtons($fb);
    attachSwipe($fb);
    updateNavButtons();
}


    /* =================================================
       NAV BUTTONS
    ================================================= */
    function attachNavButtons(fb) {
        $("#prevPage").on("click", e => {
            e.preventDefault();
            fb.turn("previous");
        });

        $("#nextPage").on("click", e => {
            e.preventDefault();
            fb.turn("next");
        });
    }

    function updateNavButtons() {
        const fb = $("#flipbook");
        if (!fb.data("turn")) return;

        const page  = fb.turn("page");
        const total = fb.turn("pages");

        $("#prevPage").css("display", page <= 1 ? "none" : "flex");
        $("#nextPage").css(
            "display",
            page >= total ? "none" : "flex"
        );
    }

    /* =================================================
       MOBILE SWIPE
    ================================================= */
    function attachSwipe(fb) {
        let startX = 0;

        fb.on("touchstart", e => {
            startX = e.originalEvent.touches[0].clientX;
        });

        fb.on("touchend", e => {
            const diff = e.originalEvent.changedTouches[0].clientX - startX;
            if (Math.abs(diff) < 40) return;
            diff < 0 ? fb.turn("next") : fb.turn("previous");
        });
    }

    /* =================================================
       ZOOM
    ================================================= */
    let scale = 1;
    const MIN = 1, MAX = 3, STEP = 0.2;
    const fbEl = document.getElementById("flipbook");

    function applyZoom(val) {
        scale = Math.max(MIN, Math.min(MAX, val));
        fbEl.style.transform = `scale(${scale})`;

        if (scale === 1) {
            $("#flipbook").turn("disable", false);
        } else {
            $("#flipbook").turn("disable", true);
        }
    }

    document.getElementById("zoomIn")?.addEventListener("click", () => applyZoom(scale + STEP));
    document.getElementById("zoomOut")?.addEventListener("click", () => applyZoom(scale - STEP));
    document.getElementById("zoomReset")?.addEventListener("click", () => applyZoom(1));

    /* =================================================
       FULLSCREEN
    ================================================= */
    const fsBtn = document.getElementById("fullscreenToggle");
    const wrapper = document.getElementById("viewer-wrapper");

    fsBtn?.addEventListener("click", () => {
        if (!document.fullscreenElement) {
            wrapper.requestFullscreen?.();
        } else {
            document.exitFullscreen?.();
        }
    });

    /* =================================================
       LOADER + START
    ================================================= */
    window.addEventListener("load", () => {

        const loader = document.getElementById("ebookLoader");
        const viewer = document.getElementById("viewer-wrapper");
        const fb = document.getElementById("flipbook");

        setupAudio();

        waitForImages(fb, () => {
            initFlipbook();
            loader.style.display = "none";
            viewer.classList.add("show");
        });

    });

})();
