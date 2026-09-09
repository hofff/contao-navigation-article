Articles within navigations
===========================

Show articles within navigation menus.

Articles can be configured within the page structure and can be selected per page and module. So it is possible to use 
different articles for different navigations modules displaying the same page.

**The "nav_hofff_navi_art" or another proper customized navigation template is required in order to display the 
associated articles.**

**Navigation sublevels can be inserted at the desired position within
the article by using the placeholder `##hofff_navi_art##` in the article.**


Configuration
-------------

By default, only articles marked as "reference article" are provided as article options. You can disable it if you want
to see all available articles by adding the following configuration to your `config.yaml`:

```yaml
hofff_contao_navigation_article:
    reference_articles_only: false
```
