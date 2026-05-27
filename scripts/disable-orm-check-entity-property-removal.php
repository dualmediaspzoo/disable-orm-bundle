<?php

declare(strict_types=1);

use DualMedia\DisableORMBundle\Attribute\DisableORM;
use PhpParser\Node\AttributeGroup;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Property;
use PhpParser\NodeFinder;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\NameResolver;
use PhpParser\ParserFactory;

$repoRoot = trim((string)shell_exec('git rev-parse --show-toplevel 2>/dev/null'));

if ('' === $repoRoot) {
    fwrite(STDERR, "ERROR: Not inside a git repository.\n");
    exit(2);
}

require_once $repoRoot.'/vendor/autoload.php';

$targetBranch = $argv[1] ?? 'develop';

$mergeBase = trim((string)shell_exec("git merge-base {$targetBranch} HEAD 2>/dev/null"));

if ('' === $mergeBase) {
    fwrite(STDERR, "ERROR: Could not find merge base with '{$targetBranch}'. Is the branch name correct?\n");
    exit(2);
}

$diffOutput = (string)shell_exec("git diff {$mergeBase} HEAD --name-only --diff-filter=MD 2>/dev/null");
$changedFiles = array_filter(
    array_map('trim', explode("\n", $diffOutput)),
    static fn (string $f) => (bool)preg_match('#src/[^/]+/Entity/.+\.php$#', $f)
);

if ([] === $changedFiles) {
    echo "No Entity files changed vs '{$targetBranch}'.\n";
    exit(0);
}

$parser = (new ParserFactory())->createForNewestSupportedVersion();
$traverser = new NodeTraverser();
$traverser->addVisitor(new NameResolver());
$finder = new NodeFinder();
$errors = [];

foreach ($changedFiles as $file) {
    $baseSrc = (string)shell_exec("git show {$mergeBase}:{$file} 2>/dev/null");

    if ('' === $baseSrc) {
        continue;
    }

    $baseProps = extractProperties($traverser->traverse($parser->parse($baseSrc) ?? []), $finder);

    $absolutePath = $repoRoot.'/'.$file;
    $currentSrc = is_file($absolutePath) ? (string)file_get_contents($absolutePath) : '';
    $currentProps = '' !== $currentSrc ? extractProperties($traverser->traverse($parser->parse($currentSrc) ?? []), $finder) : [];

    foreach ($baseProps as $className => $properties) {
        $currentClassProps = $currentProps[$className] ?? [];

        foreach ($properties as $propName => $hasDisableORM) {
            if (array_key_exists($propName, $currentClassProps)) {
                continue;
            }

            if (!$hasDisableORM) {
                $errors[$file][] = '$'.$propName;
            }
        }
    }
}

if ([] === $errors) {
    echo "[OK]: no unsafe Entity property removals vs '{$targetBranch}'.\n";
    exit(0);
}

echo sprintf("[KO] Found %d file(s) with unsafe property removal(s) vs '%s':\n\n", count($errors), $targetBranch);

foreach ($errors as $file => $fields) {
    echo "[ERROR] $file unsafe property removal\n";

    foreach ($fields as $field) {
        echo "  - $field\n";
    }

    echo "\n";
}
exit(1);

/**
 * @param \PhpParser\Node[] $ast
 *
 * @return array<string, array<string, bool>> className => [propName => hasDisableORM]
 */
function extractProperties(
    array $ast,
    NodeFinder $finder
): array {
    $result = [];

    foreach ($finder->findInstanceOf($ast, Class_::class) as $class) {
        $className = $class->name?->name ?? 'anonymous';
        $result[$className] = [];

        foreach ($finder->findInstanceOf([$class], Property::class) as $property) {
            $hasDisableORM = hasDisableORMAttribute($property->attrGroups);

            foreach ($property->props as $prop) {
                $result[$className][$prop->name->name] = $hasDisableORM;
            }
        }
    }

    return $result;
}

/**
 * @param AttributeGroup[] $attrGroups
 */
function hasDisableORMAttribute(
    array $attrGroups
): bool {
    return array_any(
        $attrGroups,
        static fn ($group) => array_any(
            $group->attrs,
            static fn ($attr) => DisableORM::class === $attr->name->toString()
        )
    );
}
