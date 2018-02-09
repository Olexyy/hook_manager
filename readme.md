Drupal 8 hook OOP implementation.

Current implementation has some advantages:

- it should be rather fast;
- it uses caching and static;
- priority is supported;
- we have progress in naming convention problems;
- we can inject services to classes that implement hooks;

Repo:
https://github.com/Olexyy/hook_manager.git

If there are any thoughts, please share.

The idea is rather simple:
Annotation to plugin contains list of canonical names for hooks with corresponding priority. (f.e. hook_theme => 0)
We need to implement this hook as method in camel case, so this is public function hookTheme();