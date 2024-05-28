#!/bin/bash

script_dir=$(realpath "$0")
script_dir=$(dirname "$script_dir")

echo +++ TEST NOTIFY +++

env | grep notification

