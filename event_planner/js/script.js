document.addEventListener("DOMContentLoaded", function () {
    let recommendedEventsDiv = document.getElementById("recommended-events");

    // Fetch recommendations when page is loaded
    fetch("php/recommend_events.php")
        .then(response => {
            if (!response.ok) {
                throw new Error("Failed to fetch recommendations.");
            }
            return response.json();
        })
        .then(data => {
            console.log("Recommended Events Data:", data); // Debugging log to check data

            // If no data or an empty array is returned
            if (!Array.isArray(data) || data.length === 0) {
                recommendedEventsDiv.innerHTML = "<p>No recommendations available.</p>";
                return;
            }

            // Clear the loading message and display the events
            recommendedEventsDiv.innerHTML = '';

            // Create event cards for each recommended event
            data.forEach(event => {
                let eventDiv = document.createElement("div");
                eventDiv.classList.add("event-card");

                eventDiv.innerHTML = `
                    <h3>${event.title}</h3>
                    <p>${event.description || "No description available"}</p>
                    <p><strong>📅 Date:</strong> ${new Date(event.start_time).toLocaleString()}</p>
                    <p><strong>📍 Location:</strong> ${event.location}</p>
                    <p><strong>🏷 Category:</strong> ${event.category}</p>
                    <hr>
                    <a href="event_details.php?id=${event.event_id}" class="btn">View Details</a>
                `;
                
                recommendedEventsDiv.appendChild(eventDiv);
            });

            console.log("Recommendations displayed successfully.");
        })
        .catch(error => {
            console.error("Error fetching recommendations:", error);
            recommendedEventsDiv.innerHTML = "<p>Error loading recommendations. Please try again later.</p>";
        });
});
