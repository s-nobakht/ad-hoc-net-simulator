# ad-hoc-net-simulator
An ad-hoc network simulator, for simulating potential attacks and defense methods.

This program reads the stored node information and displays it graphically in the browser.

# What this repo is about?
Ad-Hoc networks are networks that do not have common elements in networks such as server and client. In these networks, nodes are dynamically added to the network and the network expands as they join. Each node is both a server and a client. The main feature of these networks is their self-organization and self-adaptation. Ad-Hoc networks have many different applications in the environment, meteorology, medical affairs and disease control, etc. One of the most important issues for these networks is to prevent unauthorized access to information. One type of unauthorized access is the passage of network traffic through nodes that are not members of the network and spy on the network. In this type of attack, there are usually penetration nodes in the network that allow information to be leaked out. The most popular type of attack in this category is the wormhole attack. This code provides a way to deal with this type of attack, which is based on the automatic assignment of weights to nodes and routing based on these weights. An important advantage of this method is the lack of additional hardware, authentication, manual network configuration, as well as compatibility with network self-organization and self-adaptation features. An Ad-Hoc network simulator has been developed to evaluate this method.

# Why use PHP?
The primary goal of this project was to create a prototype and simulation of the idea of ​​counterattack, and the issue of performance was not considered in this section. Interpreting languages ​​such as PHP and Python are good choices for this purpose. PHP was finally chosen for this code for the following reasons:
1. There was another web tool with which this code was to be embedded. The tool was developed in PHP, and it was preferred that this code be developed in PHP as well.
2.At the time of this development, HTML5-based tools for producing web graphics were significantly improved. PHP works well with these features.
3. Compilers like HipHop can convert PHP code to C++ code. In other words, after ensuring the performance of the proposed algorithm, it can also be improved in terms of performance.
