# BlockAlter Plugin

## Installation
1. Copy the plugin `.phar` file to the `plugins` directory of your PocketMine server.
2. Restart the server to load the plugin.
3. Start mining away the blocks you set in the config!

## Config
```
blocks:
  stone: This is the block name that will be mined.
    world: This is the name of the world where replacements will take place.
    replace: Set this to the block that will replace the first block when mined.
    drops: To use default drops, set this value to be empty ([]); otherwise, specify desired drops.
    duration: Duration (in seconds) for the replaced block to respawn.
```

## How to Contribute?
We welcome pull requests from anyone interested in improving this project. Before making major changes, please open an issue to discuss your proposed changes and gather feedback from the project maintainers.
When submitting a pull request, please ensure that you have updated the relevant tests to reflect your changes and ensure they pass successfully.
Other than that, Thank you for your contribution!

## Credits
- [PocketAI](https://thedarkproject.net/pocketai) - Code generating AI revolving around Pocketmine-MP
