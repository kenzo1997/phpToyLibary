<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Home</title>
  <style>
    body {
      margin: 0;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      background: linear-gradient(to bottom right, #e0f2ff, #90cdf4);
      display: flex;
      align-items: center;
      justify-content: center;
      height: 100vh;
      padding: 1rem;
    }

    .home-container {
      background-color: #fff;
      padding: 2rem 3rem;
      border-radius: 1.5rem;
      box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
      text-align: center;
      max-width: 800px;
      width: 100%;
      overflow-x: auto;
    }

    h1 {
      margin-bottom: 1.5rem;
      color: #2d3748;
    }

    form {
      margin-bottom: 2rem;
    }

    form button {
      background-color: #e53e3e;
      color: white;
      border: none;
      padding: 0.75rem 1.5rem;
      font-size: 1rem;
      border-radius: 0.75rem;
      cursor: pointer;
      transition: background-color 0.2s;
    }

    form button:hover {
      background-color: #c53030;
    }

    table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 1rem;
    }

    th, td {
      padding: 0.75rem;
      border: 1px solid #e2e8f0;
      text-align: left;
    }

    th {
      background-color: #f7fafc;
      color: #2d3748;
    }

    tr:nth-child(even) {
      background-color: #f0f4f8;
    }

    tr:hover {
      background-color: #e2e8f0;
    }
  </style>
</head>
<body>
  <div class="home-container">
    <h1>Welcome <?php echo htmlspecialchars($name); ?>!</h1>
    <h2>Recent Projects</h2>
    <table>
      <thead>
        <tr>
          <th>Project Name</th>
          <th>Status</th>
          <th>Due Date</th>
          <th>Owner</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($projects as $project): ?>
        <tr>
          <td><?php echo htmlspecialchars($project['name']); ?></td>
          <td><?php echo htmlspecialchars($project['status']); ?></td>
          <td><?php echo htmlspecialchars($project['due_date']); ?></td>
          <td><?php echo htmlspecialchars($project['owner']); ?></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    <form action="/login/logout" method="post">
      <button type="submit">Logout</button>
    </form>
  </div>
</body>
</html>

