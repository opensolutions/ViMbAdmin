#!/bin/sh

git log --date=short "--format=format:%s (%h - %an - %ad)"  | grep "^\["

