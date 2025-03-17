function reInitGLightbox() {
  // Destroy the previous instance
  if (window.lightbox) {
    window.lightbox.destroy();
  }

  // Reinitialize with new content
  window.lightbox = GLightbox({
    selector: '.rixet-lightbox', // Selects all elements with the class
    touchNavigation: true, // Enable touch navigation
    loop: true, // Enable looping through items
    autoplayVideos: true, // Autoplay videos
    openEffect: 'fade', // Use fade effect for opening
    closeEffect: 'fade', // Use fade effect for closing
    slideEffect: 'slide', // Use slide effect for transitions
    caption: function (item) {
      const size = item.el.getAttribute('data-size');
      return `<div class="custom-caption"><h3>${item.title || ''}</h3>` + size != '' ? (`<p>Size: ${size || 'N/A'}</p>`) : '' + `</div>`;
    },
    videoAutoplay: true, // Autoplay video on open
    closeOnOutsideClick: true, // Close on clicking outside
  });

  document.querySelectorAll('.rixet-lightbox-video').forEach(link => {
    const video = link.querySelector("video");

    // Create and insert the image element for the thumbnail
    const img = document.createElement("img");
    img.src = video.getAttribute('data-thumbnail'); // Default thumbnail
    img.alt = "video thumbnail";
    img.classList.add('image-file');
    link.insertBefore(img, video); // Insert image before the video

    // Create and insert the canvas element for thumbnail generation
    const canvas = document.createElement("canvas");
    canvas.className = "thumbnailCanvas"; // Set class for styling
    canvas.style.display = "none"; // Keep it hidden
    link.appendChild(canvas); // Append canvas after the video

    const ctx = canvas.getContext("2d");

    // Check if the video element exists
    if (video) {
      // Wait for the video to load its metadata
      video.addEventListener("loadedmetadata", function () {
        // Seek to 1 second after the metadata has loaded
        video.currentTime = 1;
      });

      // Once the video is ready at 1 second, update the thumbnail
      video.addEventListener("seeked", function () {
        // Set canvas dimensions to the video dimensions
        canvas.width = video.videoWidth;
        canvas.height = video.videoHeight;

        // Draw the video frame onto the canvas
        ctx.drawImage(video, 0, 0, canvas.width, canvas.height);

        // Convert canvas to image and set it as the thumbnail source
        img.src = canvas.toDataURL("image/png");

        // Remove the canvas element after generating the thumbnail
        canvas.remove();
      });

      // Load the video
      video.load();
    }
  });
}

reInitGLightbox();