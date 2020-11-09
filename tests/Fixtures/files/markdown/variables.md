## simple
Variables {{Foo}} or {{Foo}}{{Bar}}.

## input
Inputs {{Foo?}}, {{Bar?}}.

## more characters
Variables {{{Foo}}}, {{{{Bar}}}}

## other contexts
Indented code:

    {{A}}, {{B?}}

Or inline `{{Foo}}` or fenced:
```
{{Foo}}
```

- {{Foo}}
- {{Bar?}}

Or inside **inline {{Foo}} blocks**.

## no variables
No spaces between braces { {Foo}} {{Foo} } as well as after braces {{ Foo }}.

Bad formats: {{Foo??}}, {{Foo?B}}, {{F oo}}  

Variables are case-sensitive: {{foo}}.

These are invalid: {{FooBar}}, {{FooBar?}}
