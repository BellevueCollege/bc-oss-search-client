# Open Search Server search client
Provides a Open Search Server based search page within WordPress through the use of a shortcode

## Configuration
1. Install plugin
2. Activate plugin on single site (ideally at /search/ location)
3. Copy `config-sample.php` and rename to `config.php`. 
4. Update `config.php` with server credentials.
   A fully configured file is available in the docs repo.
5. Place `[bc-oss-search]` shortcode on page where search should appear.
   Spelling correction (beta) can be enabled by adding `spelling="true"`
   as a parameter within the shortcode.
