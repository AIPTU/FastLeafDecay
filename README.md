# FastLeafDecay

A Pocketmine-MP plugin that accelerates leaf decay when trees are felled, enhancing gameplay by speeding up the visual impact of tree felling.

## Features
- **Accelerated Leaf Decay**: When a tree is cut down, nearby leaves will begin to decay faster, simulating the natural process of leaf fall.
- **Configurable Decay Delay**: Customize the leaf decay delay with configurable minimum and maximum values.
- **Search Radius Configuration**: Customize the radius at which leaves will be affected by tree felling.
- **Efficient Processing**: Uses a queuing system to handle leaf decay efficiently, ensuring smooth performance even with large trees.

## Default Config
```yaml
# FastLeafDecay Configuration

# The minimum delay (in seconds) before a leaf block decays after a nearby wood block is broken.
# Must be a positive integer (>= 1).
min_leaf_decay_delay: 1

# The maximum delay (in seconds) before a leaf block decays after a nearby wood block is broken.
# Must be a positive integer (>= min_leaf_decay_delay).
max_leaf_decay_delay: 5

# The maximum radius (in blocks) around the broken wood block within which leaves will be checked for decay.
# Must be a positive integer (>= 1).
max_search_radius: 6

```

## Upcoming Features

- Currently none planned. You can contribute or suggest for new features.

## Additional Notes

- If you find bugs or want to give suggestions, please visit [here](https://github.com/AIPTU/FastLeafDecay/issues).
- We accept all contributions! If you want to contribute, please make a pull request in [here](https://github.com/AIPTU/FastLeafDecay/pulls).
- Icons made from [www.flaticon.com](https://www.flaticon.com)
