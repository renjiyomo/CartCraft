document.getElementById('downloadDocBtn').addEventListener('click', function () {
    // Define the table headers
    const headers = `
        <tr>
            <th>Name</th>
            <th>Email</th>
            <th>Phone Number</th>
            <th>Date Registered</th>
            <th>Specialization</th>
            <th>Total Products</th>
            <th>Status</th>
        </tr>
    `;

    // Initialize the table content
    let tableContent = `<table style="width: 100%; border-collapse: collapse; text-align: center; font-family: Arial, sans-serif;">`;
    tableContent += `<thead style="background-color: #f2f2f2;">${headers}</thead>`;
    tableContent += `<tbody>`;

    // Loop through rows and exclude unnecessary columns
    const rows = document.querySelectorAll('.artist-row');
    rows.forEach(row => {
        const columns = row.querySelectorAll('td');
        tableContent += `<tr>`;
        // Include only relevant columns
        for (let i = 0; i < columns.length - 2; i++) { // Exclude last two columns (Valid ID and Actions)
            tableContent += `<td style="padding: 8px; border: 1px solid #ddd;">${columns[i].textContent}</td>`;
        }
        tableContent += `</tr>`;
    });

    tableContent += `</tbody></table>`;

    // Create a Word document with A4 layout
    const docContent = `
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <style>
                @page {
                    size: A4; /* Set page size to A4 */
                    margin: 20mm; /* Add margins for proper spacing */
                }
                body {
                    font-family: Arial, sans-serif;
                    margin: 0;
                    padding: 0;
                }
                .content {
                    width: 100%;
                    margin: auto;
                    text-align: center;
                }
                h2 {
                    text-align: center;
                    font-size: 24px;
                    margin-bottom: 20px;
                }
                table {
                    width: 100%;
                    border-collapse: collapse;
                    margin: 0 auto;
                }
                th, td {
                    border: 1px solid #ddd;
                    padding: 8px;
                }
                th {
                    background-color: #f2f2f2;
                    text-align: center;
                }
                td {
                    text-align: center;
                }
            </style>
        </head>
        <body>
            <div class="content">
                <h2>Artist Information</h2>
                ${tableContent}
            </div>
        </body>
        </html>
    `;

    // Convert the document to a downloadable Blob
    const blob = new Blob([docContent], { type: 'application/msword' });
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = 'ArtistInfo-CartCraft.doc';
    a.click();
    URL.revokeObjectURL(url);
});