<?php if (isset($_SESSION['user'])): ?>
    <footer>
        <!--
            <div style="display: flex;">        
                Calendar Feeds:
            </div>
        -->
        <br>
        <a class="button" href="http://cop4710/logout">Logout</a>
    </footer>

    <?php if (isset($queryText)): 
        foreach ($queryText as $query): ?>
            <script>
                console.log(`<?php echo $query; ?>`);
            </script>
    <?php endforeach; endif; ?>
<?php endif; ?>
</body>
</html>