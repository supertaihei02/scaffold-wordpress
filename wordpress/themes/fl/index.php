<?php
list($template, $arguments) = CustomizerTwig::currentPage();
CustomizerTwig::prepare()->display($template, $arguments);
